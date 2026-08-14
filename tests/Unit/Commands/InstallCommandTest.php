<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\InstallCommand;
use App\Models\User;
use Illuminate\Console\OutputStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_preserves_an_existing_valid_application_key(): void
    {
        config()->set('app.key', 'base64:'.base64_encode(random_bytes(32)));
        config()->set('app.cipher', 'AES-256-CBC');

        Artisan::shouldReceive('call')->never();

        [$result, $output] = $this->generateApplicationKey();

        $this->assertTrue($result);
        $this->assertStringContainsString('Application key already configured.', $output);
    }

    public function test_it_generates_an_application_key_without_force_when_key_is_empty(): void
    {
        config()->set('app.key', '');

        Artisan::shouldReceive('call')
            ->once()
            ->with('key:generate', [])
            ->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('');

        [$result] = $this->generateApplicationKey();

        $this->assertTrue($result);
    }

    public function test_it_replaces_an_invalid_application_key_conditionally(): void
    {
        config()->set('app.key', 'invalid-key');

        Artisan::shouldReceive('call')
            ->once()
            ->with('key:generate', ['--force' => true])
            ->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('');

        [$result] = $this->generateApplicationKey();

        $this->assertTrue($result);
    }

    public function test_migrations_remain_safe_to_resume_with_force(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('migrate', ['--force' => true])
            ->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('Nothing to migrate.');

        [$result] = $this->invokeInstallerStep('runMigrations');

        $this->assertTrue($result);
    }

    public function test_it_clears_application_caches(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize:clear')
            ->andReturn(0);
        Artisan::shouldReceive('output')->once()->andReturn('Caches cleared.');

        [$result, $output] = $this->invokeInstallerStep('clearApplicationCaches');

        $this->assertTrue($result);
        $this->assertStringContainsString('Caches cleared.', $output);
    }

    public function test_cache_clear_failure_fails_the_installer_step(): void
    {
        Artisan::shouldReceive('call')
            ->once()
            ->with('optimize:clear')
            ->andReturn(1);
        Artisan::shouldReceive('output')->once()->andReturn('Cache clear failed.');

        [$result, $output] = $this->invokeInstallerStep('clearApplicationCaches');

        $this->assertFalse($result);
        $this->assertStringContainsString('Failed to clear application caches.', $output);
    }

    public function test_it_preserves_an_existing_administrator_when_resuming_installation(): void
    {
        $administrator = User::factory()->create([
            'is_admin' => true,
            'active' => true,
        ]);

        [$result, $output] = $this->invokeInstallerStep('createAdminUser');

        $this->assertTrue($result);
        $this->assertStringContainsString('Administrator user already configured.', $output);
        $this->assertSame(1, User::query()->where('is_admin', true)->count());
        $this->assertDatabaseHas('users', [
            'id' => $administrator->id,
            'email' => $administrator->email,
        ]);
    }

    public function test_administrator_password_requires_at_least_twelve_characters(): void
    {
        $this->assertFalse($this->isValidAdminPassword('short-pass', 'short-pass'));
    }

    public function test_administrator_password_requires_matching_confirmation(): void
    {
        $this->assertFalse($this->isValidAdminPassword(
            'a secure password',
            'a different password',
        ));
    }

    public function test_administrator_password_confirmation_is_required(): void
    {
        $this->assertFalse($this->isValidAdminPassword('a secure password', ''));
    }

    public function test_administrator_password_accepts_twelve_characters_without_arbitrary_complexity(): void
    {
        $this->assertTrue($this->isValidAdminPassword('abcdefghijkl', 'abcdefghijkl'));
    }

    public function test_non_interactive_admin_configuration_fails_clearly_when_options_are_missing(): void
    {
        [$command, $buffer] = $this->makeCommand([], false);

        $method = new ReflectionMethod($command, 'resolveNonInteractiveAdminCredentials');
        $result = $method->invoke($command);

        $this->assertNull($result);
        $this->assertStringContainsString(
            'Missing required administrator options in non-interactive mode: --admin-name, --admin-email, --admin-password.',
            $buffer->fetch(),
        );
    }

    public function test_non_interactive_database_configuration_fails_clearly_when_required_values_are_missing(): void
    {
        [$command, $buffer] = $this->makeCommand([], false);

        $method = new ReflectionMethod($command, 'resolveNonInteractiveDatabaseConfiguration');
        $result = $method->invoke($command, [
            'DB_HOST' => '',
            'DB_PORT' => '',
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ]);

        $this->assertNull($result);
        $this->assertStringContainsString(
            'Missing required database options in non-interactive mode: --db-host, --db-port, --db-database, --db-username.',
            $buffer->fetch(),
        );
    }

    public function test_non_interactive_admin_configuration_uses_explicit_options(): void
    {
        [$command] = $this->makeCommand([
            '--admin-name' => 'Admin User',
            '--admin-email' => 'ADMIN@example.com',
            '--admin-password' => 'abcdefghijkl',
        ], false);

        $method = new ReflectionMethod($command, 'resolveNonInteractiveAdminCredentials');

        $this->assertSame([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => 'abcdefghijkl',
        ], $method->invoke($command));
    }

    public function test_non_interactive_module_configuration_uses_flags_without_prompting(): void
    {
        [$command] = $this->makeCommand([
            '--enable-customer' => true,
            '--enable-vod' => true,
        ], false);

        $method = new ReflectionMethod($command, 'selectOptionalModules');

        $this->assertSame(
            ['customer', 'vod'],
            $method->invoke($command, ['customer' => 'Customer', 'vod' => 'VOD'], []),
        );
    }

    /**
     * @return array{bool, string}
     */
    private function generateApplicationKey(): array
    {
        return $this->invokeInstallerStep('generateApplicationKey');
    }

    /**
     * @return array{bool, string}
     */
    private function invokeInstallerStep(string $methodName): array
    {
        [$command, $buffer] = $this->makeCommand();

        $method = new ReflectionMethod($command, $methodName);
        $result = $method->invoke($command);

        return [$result, $buffer->fetch()];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{InstallCommand, BufferedOutput}
     */
    private function makeCommand(array $options = [], bool $interactive = true): array
    {
        $command = new InstallCommand;
        $input = new ArrayInput($options, $command->getDefinition());
        $input->setInteractive($interactive);
        $buffer = new BufferedOutput;

        $inputProperty = new ReflectionProperty($command, 'input');
        $inputProperty->setValue($command, $input);

        $outputProperty = new ReflectionProperty($command, 'output');
        $outputProperty->setValue($command, new OutputStyle($input, $buffer));

        return [$command, $buffer];
    }

    private function isValidAdminPassword(string $password, string $confirmation): bool
    {
        $command = new InstallCommand;
        $method = new ReflectionMethod($command, 'isValidAdminPassword');

        return $method->invoke($command, $password, $confirmation);
    }
}
