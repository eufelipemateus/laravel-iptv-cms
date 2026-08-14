<?php

namespace App\Console\Commands;

use App\Actions\Users\CreateUserAdmin;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use PDOException;
use Throwable;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install application when APP_ENV=install.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->environment('install')) {
            $this->error('This command can only be executed when APP_ENV=install.');

            return self::FAILURE;
        }

        if (! $this->ensureEnvFileIsWritable()) {
            return self::FAILURE;
        }

        $dbConfig = $this->resolveDatabaseConfiguration();

        if ($dbConfig === null) {
            return self::FAILURE;
        }

        if (! $this->applyDatabaseConfiguration($dbConfig)) {
            return self::FAILURE;
        }

        if (! $this->generateApplicationKey()) {
            return self::FAILURE;
        }

        if (! $this->runMigrations()) {
            return self::FAILURE;
        }

        if (! $this->configureModules()) {
            return self::FAILURE;
        }

        if (! $this->createAdminUser()) {
            return self::FAILURE;
        }

        if (! $this->switchApplicationEnvironmentToStore()) {
            return self::FAILURE;
        }

        $this->info('Installation completed successfully.');

        return self::SUCCESS;
    }

    private function ensureEnvFileIsWritable(): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error('.env file not found.');

            return false;
        }

        if (! is_writable($envPath)) {
            $this->error('No write permission for .env file. Adjust permissions and try again.');

            return false;
        }

        return true;
    }

    /**
     * @return array<string, string>|null
     */
    private function resolveDatabaseConfiguration(): ?array
    {
        $currentConfig = [
            'DB_HOST' => (string) env('DB_HOST', ''),
            'DB_PORT' => (string) env('DB_PORT', ''),
            'DB_DATABASE' => (string) env('DB_DATABASE', ''),
            'DB_USERNAME' => (string) env('DB_USERNAME', ''),
            'DB_PASSWORD' => (string) env('DB_PASSWORD', ''),
        ];

        if ($this->hasDatabaseValues($currentConfig)) {
            $this->info('Validating current database connection...');

            $currentConnectionError = $this->testDatabaseConnection($currentConfig);

            if ($currentConnectionError === null) {
                $this->info('Database connection validated successfully.');

                return $currentConfig;
            }

            $this->warn('Current database connection is invalid: '.$currentConnectionError);
        } else {
            $this->warn('Database settings are not configured in .env yet.');
        }

        while (true) {
            $dbConfig = $this->askDatabaseConfiguration();
            $connectionError = $this->testDatabaseConnection($dbConfig);

            if ($connectionError !== null) {
                $this->error('Failed to connect to the database: '.$connectionError);
                $this->warn('Please provide the database credentials again.');

                continue;
            }

            if (! $this->persistEnvValues($dbConfig)) {
                return null;
            }

            $this->info('Database configuration was saved to .env successfully.');

            return $dbConfig;
        }
    }

    /**
     * @param  array<string, string>  $config
     */
    private function hasDatabaseValues(array $config): bool
    {
        return $config['DB_HOST'] !== ''
            && $config['DB_PORT'] !== ''
            && $config['DB_DATABASE'] !== ''
            && $config['DB_USERNAME'] !== '';
    }

    /**
     * @return array<string, string>
     */
    private function askDatabaseConfiguration(): array
    {
        return [
            'DB_HOST' => (string) $this->ask('Database host', '127.0.0.1'),
            'DB_PORT' => (string) $this->ask('Database port', '3306'),
            'DB_DATABASE' => (string) $this->askRequired('Database name'),
            'DB_USERNAME' => (string) $this->askRequired('Database username'),
            'DB_PASSWORD' => (string) $this->secret('Database password') ?? '',
        ];
    }

    private function askRequired(string $question): string
    {
        if (! $this->input->isInteractive()) {
            $this->error('Missing required value in non-interactive mode: '.$question.'.');

            throw new \RuntimeException('Required input is missing in non-interactive mode.');
        }

        $maxAttempts = 3;
        $attempts = 0;

        while (true) {
            if ($attempts >= $maxAttempts) {
                throw new \RuntimeException('Too many empty attempts while reading: '.$question.'.');
            }

            $value = trim((string) $this->ask($question));

            if ($value !== '') {
                return $value;
            }

            $this->error('This field is required.');
            $attempts++;
        }
    }

    /**
     * @param  array<string, string>  $dbConfig
     */
    private function testDatabaseConnection(array $dbConfig): ?string
    {
        $driver = (string) env('DB_CONNECTION', config('database.default', 'mysql'));

        $temporaryConfig = [
            'driver' => $driver,
            'host' => $dbConfig['DB_HOST'],
            'port' => $dbConfig['DB_PORT'],
            'database' => $dbConfig['DB_DATABASE'],
            'username' => $dbConfig['DB_USERNAME'],
            'password' => $dbConfig['DB_PASSWORD'],
            'charset' => config('database.connections.mysql.charset', 'utf8mb4'),
            'collation' => config('database.connections.mysql.collation', 'utf8mb4_unicode_ci'),
            'prefix' => '',
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
            ]) : [],
        ];

        config(['database.connections.install_test' => $temporaryConfig]);
        DB::purge('install_test');

        try {
            DB::connection('install_test')->getPdo();

            return null;
        } catch (PDOException $exception) {
            return $exception->getMessage();
        } catch (Throwable $exception) {
            return $exception->getMessage();
        } finally {
            DB::disconnect('install_test');
            DB::purge('install_test');
            config()->offsetUnset('database.connections.install_test');
        }
    }

    /**
     * @param  array<string, string>  $dbConfig
     */
    private function applyDatabaseConfiguration(array $dbConfig): bool
    {
        $connection = (string) config('database.default');

        config([
            "database.connections.{$connection}.host" => $dbConfig['DB_HOST'],
            "database.connections.{$connection}.port" => $dbConfig['DB_PORT'],
            "database.connections.{$connection}.database" => $dbConfig['DB_DATABASE'],
            "database.connections.{$connection}.username" => $dbConfig['DB_USERNAME'],
            "database.connections.{$connection}.password" => $dbConfig['DB_PASSWORD'],
        ]);

        DB::purge($connection);

        try {
            DB::reconnect($connection)->getPdo();

            return true;
        } catch (Throwable $exception) {
            $this->error('Could not apply the database configuration: '.$exception->getMessage());

            return false;
        }
    }

    /**
     * @param  array<string, string>  $values
     */
    private function persistEnvValues(array $values): bool
    {
        $envPath = base_path('.env');

        if (! file_exists($envPath)) {
            $this->error('.env file not found.');

            return false;
        }

        $envContent = file_get_contents($envPath);

        if ($envContent === false) {
            $this->error('Could not read .env file.');

            return false;
        }

        foreach ($values as $key => $value) {
            $encodedValue = $this->encodeEnvValue($value);
            $pattern = '/^'.preg_quote($key, '/').'=.*/m';
            $replacement = $key.'='.$encodedValue;

            if (preg_match($pattern, $envContent) === 1) {
                $envContent = (string) preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= PHP_EOL.$replacement;
            }

            $_ENV[$key] = $value;
            $_SERVER[$key] = $value;
            putenv($key.'='.$value);
        }

        return file_put_contents($envPath, $envContent) !== false;
    }

    private function encodeEnvValue(string $value): string
    {
        if ($value === '') {
            return '""';
        }

        if (preg_match('/\s|#|"|\'/', $value) === 1) {
            return '"'.addslashes($value).'"';
        }

        return $value;
    }

    private function runMigrations(): bool
    {
        $this->info('Running migrations...');

        $exitCode = Artisan::call('migrate', ['--force' => true]);
        $this->output->write(Artisan::output());

        if ($exitCode !== self::SUCCESS) {
            $this->error('Failed to run migrations.');

            return false;
        }

        return true;
    }

    private function generateApplicationKey(): bool
    {
        $key = (string) config('app.key', '');

        if ($this->hasValidApplicationKey($key)) {
            $this->info('Application key already configured.');

            return true;
        }

        $this->info('Generating application key...');

        $options = $key === '' ? [] : ['--force' => true];
        $exitCode = Artisan::call('key:generate', $options);
        $this->output->write(Artisan::output());

        if ($exitCode !== self::SUCCESS) {
            $this->error('Failed to generate application key.');

            return false;
        }

        return true;
    }

    private function hasValidApplicationKey(string $key): bool
    {
        if (Str::startsWith($key, 'base64:')) {
            $decodedKey = base64_decode(Str::after($key, 'base64:'), true);

            if ($decodedKey === false) {
                return false;
            }

            $key = $decodedKey;
        }

        return Encrypter::supported($key, (string) config('app.cipher'));
    }

    private function createAdminUser(): bool
    {
        if (User::query()->where('is_admin', true)->exists()) {
            $this->info('Administrator user already configured.');

            return true;
        }

        $name = $this->askRequired('Administrator name');

        $email = $this->askAdminEmail();
        $password = $this->askAdminPassword();

        try {
            CreateUserAdmin::run([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ]);

            $this->info('Administrator user created successfully.');

            return true;
        } catch (Throwable $exception) {
            $this->error('Could not create administrator user: '.$exception->getMessage());

            return false;
        }
    }

    private function askAdminEmail(): string
    {
        while (true) {
            $email = trim((string) $this->ask('Administrator email'));

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('Invalid email. Please provide a valid email address.');

                continue;
            }

            if (User::query()->where('email', $email)->exists()) {
                $this->error('A user with this email already exists. Please provide a different email.');

                continue;
            }

            return Str::lower($email);
        }
    }

    private function askAdminPassword(): string
    {
        while (true) {
            $password = (string) ($this->secret('Administrator password') ?? '');
            $passwordConfirmation = (string) ($this->secret('Repeat administrator password') ?? '');

            if (! $this->isValidAdminPassword($password, $passwordConfirmation)) {
                $this->error('Password must contain at least 12 characters and match its confirmation.');

                continue;
            }

            return $password;
        }
    }

    private function isValidAdminPassword(string $password, string $passwordConfirmation): bool
    {
        return Validator::make([
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'password' => ['required', 'confirmed', Password::min(12)],
            'password_confirmation' => ['required'],
        ])->passes();
    }

    private function switchApplicationEnvironmentToStore(): bool
    {
        $this->info('Updating APP_ENV to store...');

        $updated = $this->persistEnvValues([
            'APP_ENV' => 'store',
        ]);

        if (! $updated) {
            $this->error('Could not update APP_ENV in .env.');

            return false;
        }

        return true;
    }

    private function configureModules(): bool
    {
        $this->info('Configuring modules...');
        $optionalModules = [
            'customer' => 'Customer',
            'vod' => 'VOD',
        ];

        $defaultModules = [];

        if ((bool) env('MODULE_CUSTOMER_ENABLED', false)) {
            $defaultModules[] = 'customer';
        }

        if ((bool) env('MODULE_VOD_ENABLED', false)) {
            $defaultModules[] = 'vod';
        }

        $selectedModules = $this->selectOptionalModules($optionalModules, $defaultModules);

        $saved = $this->persistEnvValues([
            'MODULE_CUSTOMER_ENABLED' => in_array('customer', $selectedModules, true) ? 'true' : 'false',
            'MODULE_VOD_ENABLED' => in_array('vod', $selectedModules, true) ? 'true' : 'false',
        ]);

        if (! $saved) {
            $this->error('Could not save module configuration in .env.');

            return false;
        }

        $this->info('Module configuration saved successfully.');

        return true;
    }

    /**
     * @param  array<string, string>  $optionalModules
     * @param  array<int, string>  $defaultModules
     * @return array<int, string>
     */
    private function selectOptionalModules(array $optionalModules, array $defaultModules): array
    {
        if (! $this->input->isInteractive()) {
            $this->warn('Non-interactive mode detected. Using current module defaults from .env.');

            return $defaultModules;
        }

        $presetOptions = [
            'none' => 'None',
            'customer' => 'Customer',
            'vod' => 'VOD',
            'customer,vod' => 'Customer + VOD',
        ];

        $defaultPreset = $this->buildModulePresetDefault($defaultModules);

        $selectedPreset = (string) $this->choice(
            'Select modules to enable (use keyboard arrows and Enter)',
            $presetOptions,
            $defaultPreset,
        );

        return match ($selectedPreset) {
            'customer' => ['customer'],
            'vod' => ['vod'],
            'customer,vod' => ['customer', 'vod'],
            default => [],
        };
    }

    /**
     * @param  array<int, string>  $defaultModules
     */
    private function buildModulePresetDefault(array $defaultModules): string
    {
        $hasCustomer = in_array('customer', $defaultModules, true);
        $hasVod = in_array('vod', $defaultModules, true);

        if ($hasCustomer && $hasVod) {
            return 'customer,vod';
        }

        if ($hasCustomer) {
            return 'customer';
        }

        if ($hasVod) {
            return 'vod';
        }

        return 'none';
    }
}
