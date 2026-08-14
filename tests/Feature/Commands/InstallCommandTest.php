<?php

namespace Tests\Feature\Commands;

use App\Models\User;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Testing\PendingCommand;
use Mockery;
use PHPUnit\Framework\Assert;
use ReflectionMethod;
use RuntimeException;
use stdClass;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    private string $originalBasePath;

    private string $installationDirectory;

    private string $databasePath;

    /** @var array<string, array{process: string|false, env: mixed, server: mixed}> */
    private array $originalEnvironment = [];

    private stdClass $migrationProbe;

    private stdClass $cacheClearProbe;

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBasePath = $this->app->basePath();
        $this->installationDirectory = sys_get_temp_dir().'/iptv-install-'.bin2hex(random_bytes(8));
        $this->databasePath = $this->installationDirectory.'/database.sqlite';

        mkdir($this->installationDirectory, 0700, true);
        touch($this->databasePath);

        foreach ($this->environmentKeys() as $key) {
            $this->originalEnvironment[$key] = [
                'process' => getenv($key),
                'env' => $_ENV[$key] ?? null,
                'server' => $_SERVER[$key] ?? null,
            ];
        }

        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite.database', $this->databasePath);
        DB::purge('sqlite');

        // Prepare the same isolated database the installer will receive. The migrate
        // call made by the installer itself is asserted separately in each test.
        Artisan::call('migrate', ['--force' => true]);

        file_put_contents($this->installationDirectory.'/.env', implode(PHP_EOL, [
            'APP_ENV=install',
            'APP_KEY='.config('app.key'),
            'DB_CONNECTION=sqlite',
            'DB_HOST=',
            'DB_PORT=',
            'DB_DATABASE=',
            'DB_USERNAME=',
            'DB_PASSWORD=',
            'MODULE_CUSTOMER_ENABLED=false',
            'MODULE_VOD_ENABLED=false',
            '',
        ]));

        $this->app->setBasePath($this->installationDirectory);
        $this->app->instance('env', 'install');
    }

    protected function tearDown(): void
    {
        $this->app->setBasePath($this->originalBasePath);
        DB::disconnect('sqlite');

        foreach ($this->originalEnvironment as $key => $values) {
            if ($values['process'] === false) {
                putenv($key);
            } else {
                putenv($key.'='.$values['process']);
            }

            $this->restoreEnvironmentArrayValue($_ENV, $key, $values['env']);
            $this->restoreEnvironmentArrayValue($_SERVER, $key, $values['server']);
        }

        @unlink($this->installationDirectory.'/.env');
        @unlink($this->databasePath);
        @rmdir($this->installationDirectory);

        parent::tearDown();
    }

    public function test_command_fails_outside_install_environment(): void
    {
        $this->app->instance('env', 'testing');

        $this->artisan('install')
            ->expectsOutput('This command can only be executed when APP_ENV=install.')
            ->assertExitCode(1);
    }

    public function test_interactive_command_runs_the_complete_installation_flow(): void
    {
        $applicationKey = (string) config('app.key');
        $this->setEnvironmentValue('DB_HOST', '');
        $this->setEnvironmentValue('DB_PORT', '');
        $this->setEnvironmentValue('DB_DATABASE', '');
        $this->setEnvironmentValue('DB_USERNAME', '');
        $this->setEnvironmentValue('DB_PASSWORD', '');

        $this->expectMigrationAfterDatabaseConfiguration();

        $this->artisan('install')
            ->expectsQuestion('Database host', 'localhost')
            ->expectsQuestion('Database port', '3306')
            ->expectsQuestion('Database name', $this->databasePath)
            ->expectsQuestion('Database username', 'installer')
            ->expectsQuestion('Database password', '')
            ->expectsChoice(
                'Select modules to enable (use keyboard arrows and Enter)',
                'customer,vod',
                [
                    'none' => 'None',
                    'customer' => 'Customer',
                    'vod' => 'VOD',
                    'customer,vod' => 'Customer + VOD',
                ],
            )
            ->expectsQuestion('Administrator name', 'Installation Admin')
            ->expectsQuestion('Administrator email', 'ADMIN@example.com')
            ->expectsQuestion('Administrator password', 'a-secure-password')
            ->expectsQuestion('Repeat administrator password', 'a-secure-password')
            ->expectsChoice(
                'Select the final application environment',
                'store',
                [
                    'production' => 'Production',
                    'local' => 'Local',
                    'store' => 'Store',
                    'other' => 'Other',
                ],
            )
            ->expectsOutput('Installation completed successfully.')
            ->assertSuccessful();

        $this->assertSame(1, $this->migrationProbe->calls);
        $this->assertSame(1, $this->cacheClearProbe->calls);

        $administrator = User::query()->sole();

        $this->assertTrue($administrator->is_admin);
        $this->assertTrue($administrator->active);
        $this->assertSame('admin@example.com', $administrator->email);
        $this->assertEnvContains('MODULE_CUSTOMER_ENABLED="true"');
        $this->assertEnvContains('MODULE_VOD_ENABLED="true"');
        $this->assertEnvContains('APP_KEY='.$applicationKey);
        $this->assertEnvContains('APP_ENV="store"');
    }

    public function test_non_interactive_reinstallation_uses_options_and_does_not_duplicate_admin(): void
    {
        $administrator = User::factory()->create([
            'is_admin' => true,
            'active' => true,
        ]);
        $applicationKey = (string) config('app.key');
        $this->setEnvironmentValue('MODULE_CUSTOMER_ENABLED', 'false');
        $this->setEnvironmentValue('MODULE_VOD_ENABLED', 'false');

        $this->expectMigrationAfterDatabaseConfiguration();

        $this->artisan('install', [
            '--no-interaction' => true,
            '--db-host' => 'localhost',
            '--db-port' => '3306',
            '--db-database' => $this->databasePath,
            '--db-username' => 'installer',
            '--db-password' => 'database-secret',
            '--admin-name' => 'Unused Admin',
            '--admin-email' => 'unused@example.com',
            '--admin-password' => 'a-secure-password',
            '--enable-vod' => true,
        ])
            ->expectsOutput('Administrator user already configured.')
            ->expectsOutput('Installation completed successfully.')
            ->assertSuccessful();

        $this->assertSame(1, $this->migrationProbe->calls);
        $this->assertSame(1, $this->cacheClearProbe->calls);

        $this->assertSame(1, User::query()->where('is_admin', true)->count());
        $this->assertDatabaseHas('users', ['id' => $administrator->id]);
        $this->assertEnvContains('MODULE_CUSTOMER_ENABLED="false"');
        $this->assertEnvContains('MODULE_VOD_ENABLED="true"');
        $this->assertEnvContains('APP_KEY='.$applicationKey);
    }

    public function test_non_interactive_reinstallation_preserves_modules_without_explicit_options(): void
    {
        User::factory()->create(['is_admin' => true, 'active' => true]);
        $this->setEnvironmentValue('MODULE_CUSTOMER_ENABLED', 'true');
        $this->setEnvironmentValue('MODULE_VOD_ENABLED', 'true');
        $this->expectMigrationAfterDatabaseConfiguration();

        $this->installerWithRequiredOptions()
            ->expectsOutput('Installation completed successfully.')
            ->assertSuccessful();

        $this->assertEnvContains('MODULE_CUSTOMER_ENABLED="true"');
        $this->assertEnvContains('MODULE_VOD_ENABLED="true"');
    }

    public function test_contradictory_module_options_fail_before_installation_starts(): void
    {
        $this->artisan('install', [
            '--no-interaction' => true,
            '--enable-vod' => true,
            '--disable-vod' => true,
        ])
            ->expectsOutput('Options --enable-vod and --disable-vod cannot be used together.')
            ->assertExitCode(1);
    }

    public function test_non_interactive_installation_uses_explicit_final_environment(): void
    {
        User::factory()->create(['is_admin' => true, 'active' => true]);
        $this->expectMigrationAfterDatabaseConfiguration('production');

        $this->artisan('install', [
            '--no-interaction' => true,
            '--db-host' => 'localhost',
            '--db-port' => '3306',
            '--db-database' => $this->databasePath,
            '--db-username' => 'installer',
            '--db-password' => 'database-secret',
            '--app-env' => 'production',
        ])
            ->expectsOutput('Updating APP_ENV to production...')
            ->assertSuccessful();

        $this->assertEnvContains('APP_ENV="production"');
    }

    public function test_database_connection_exception_details_are_not_written_to_the_terminal(): void
    {
        $exception = new RuntimeException('PDO leaked host=10.0.0.8 database=private username=root');
        $connection = Mockery::mock();
        $connection->shouldReceive('getPdo')->once()->andThrow($exception);

        DB::shouldReceive('purge')->twice()->with('install_test');
        DB::shouldReceive('connection')->once()->with('install_test')->andReturn($connection);
        DB::shouldReceive('disconnect')->once()->with('install_test');
        DB::shouldReceive('disconnect')->once()->with('sqlite');
        Log::shouldReceive('error')->once()->with(
            'Database connection failed during installation.',
            Mockery::on(fn (array $context): bool => $context === ['exception' => $exception]),
        );

        $this->installerWithRequiredOptions()
            ->expectsOutput('Failed to connect to the database. Verify the credentials and try again.')
            ->doesntExpectOutputToContain('10.0.0.8')
            ->doesntExpectOutputToContain('private')
            ->doesntExpectOutputToContain('root')
            ->assertExitCode(1);
    }

    public function test_apply_database_exception_details_are_not_written_to_the_terminal(): void
    {
        $exception = new RuntimeException('PDO leaked driver=mysql port=3306 host=db.internal');
        $connectionTest = Mockery::mock();
        $connectionTest->shouldReceive('getPdo')->once()->andReturn(new stdClass);
        $configuredConnection = Mockery::mock();
        $configuredConnection->shouldReceive('getPdo')->once()->andThrow($exception);

        DB::shouldReceive('purge')->twice()->with('install_test');
        DB::shouldReceive('connection')->once()->with('install_test')->andReturn($connectionTest);
        DB::shouldReceive('disconnect')->once()->with('install_test');
        DB::shouldReceive('purge')->once()->with('sqlite');
        DB::shouldReceive('reconnect')->once()->with('sqlite')->andReturn($configuredConnection);
        DB::shouldReceive('disconnect')->once()->with('sqlite');
        Log::shouldReceive('error')->once()->with(
            'Applying the database configuration failed during installation.',
            Mockery::on(fn (array $context): bool => $context === ['exception' => $exception]),
        );

        $this->installerWithRequiredOptions()
            ->expectsOutput('Could not apply the database configuration.')
            ->doesntExpectOutputToContain('mysql')
            ->doesntExpectOutputToContain('3306')
            ->doesntExpectOutputToContain('db.internal')
            ->assertExitCode(1);
    }

    public function test_cache_clear_failure_makes_the_install_command_fail(): void
    {
        User::factory()->create([
            'is_admin' => true,
            'active' => true,
        ]);
        $this->expectMigrationAfterDatabaseConfiguration();
        $this->expectCacheClearAfterEnvPersistence(1);

        $this->installerWithRequiredOptions()
            ->expectsOutput('Failed to clear application caches.')
            ->doesntExpectOutput('Installation completed successfully.')
            ->assertExitCode(1);

        $this->assertSame(1, $this->cacheClearProbe->calls);
        $this->assertEnvContains('APP_ENV="store"');
    }

    private function expectMigrationAfterDatabaseConfiguration(string $finalEnvironment = 'store'): void
    {
        $this->migrationProbe = (object) ['calls' => 0];
        $probe = $this->migrationProbe;
        $databasePath = $this->databasePath;

        $migrationCommand = new ClosureCommand('migrate {--force}', function () use ($probe, $databasePath): int {
            $probe->calls++;
            Assert::assertTrue((bool) $this->option('force'));
            Assert::assertSame($databasePath, config('database.connections.sqlite.database'));
            Assert::assertSame('installer', config('database.connections.sqlite.username'));
            Assert::assertSame($databasePath, DB::connection()->getDatabaseName());

            $this->line('Nothing to migrate.');

            return 0;
        });

        $getArtisan = new ReflectionMethod($this->app->make(Kernel::class), 'getArtisan');
        $getArtisan->invoke($this->app->make(Kernel::class))->add($migrationCommand);

        $this->expectCacheClearAfterEnvPersistence(finalEnvironment: $finalEnvironment);
    }

    private function expectCacheClearAfterEnvPersistence(int $exitCode = 0, string $finalEnvironment = 'store'): void
    {
        $this->cacheClearProbe = (object) ['calls' => 0];
        $probe = $this->cacheClearProbe;
        $envPath = $this->installationDirectory.'/.env';

        $cacheClearCommand = new ClosureCommand('optimize:clear', function () use ($probe, $envPath, $exitCode, $finalEnvironment): int {
            $probe->calls++;
            $contents = file_get_contents($envPath);

            Assert::assertIsString($contents);
            Assert::assertStringContainsString('APP_ENV="'.$finalEnvironment.'"'.PHP_EOL, $contents);

            return $exitCode;
        });

        $getArtisan = new ReflectionMethod($this->app->make(Kernel::class), 'getArtisan');
        $getArtisan->invoke($this->app->make(Kernel::class))->add($cacheClearCommand);
    }

    private function assertEnvContains(string $line): void
    {
        $contents = file_get_contents($this->installationDirectory.'/.env');

        $this->assertIsString($contents);
        $this->assertStringContainsString($line.PHP_EOL, $contents);
    }

    private function installerWithRequiredOptions(): PendingCommand
    {
        return $this->artisan('install', [
            '--no-interaction' => true,
            '--db-host' => 'localhost',
            '--db-port' => '3306',
            '--db-database' => $this->databasePath,
            '--db-username' => 'installer',
            '--db-password' => 'database-secret',
            '--admin-name' => 'Installation Admin',
            '--admin-email' => 'admin@example.com',
            '--admin-password' => 'a-secure-password',
        ]);
    }

    private function setEnvironmentValue(string $key, string $value): void
    {
        putenv($key.'='.$value);
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }

    /** @param array<string, mixed> $environment */
    private function restoreEnvironmentArrayValue(array &$environment, string $key, mixed $value): void
    {
        if ($value === null) {
            unset($environment[$key]);

            return;
        }

        $environment[$key] = $value;
    }

    /** @return list<string> */
    private function environmentKeys(): array
    {
        return [
            'APP_ENV',
            'DB_HOST',
            'DB_PORT',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
            'MODULE_CUSTOMER_ENABLED',
            'MODULE_VOD_ENABLED',
        ];
    }
}
