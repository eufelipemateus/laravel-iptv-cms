<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\InstallCommand;
use Dotenv\Dotenv;
use Illuminate\Console\OutputStyle;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class InstallCommandEnvFileTest extends TestCase
{
    private string $originalBasePath;

    private string $temporaryDirectory;

    private string $envPath;

    /** @var array<string, array{process: string|false, env: mixed, server: mixed}> */
    private array $originalEnvironment = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalBasePath = $this->app->basePath();
        $this->temporaryDirectory = sys_get_temp_dir().'/install-env-'.bin2hex(random_bytes(8));
        $this->envPath = $this->temporaryDirectory.'/.env';

        mkdir($this->temporaryDirectory, 0700, true);
        file_put_contents($this->envPath, "APP_ENV=install\nATOMIC_EXISTING_KEY=value\n");
        chmod($this->envPath, 0640);
        $this->app->setBasePath($this->temporaryDirectory);

        foreach ($this->environmentKeys() as $key) {
            $this->originalEnvironment[$key] = [
                'process' => getenv($key),
                'env' => $_ENV[$key] ?? null,
                'server' => $_SERVER[$key] ?? null,
            ];
        }
    }

    protected function tearDown(): void
    {
        $this->app->setBasePath($this->originalBasePath);

        foreach ($this->originalEnvironment as $key => $values) {
            $values['process'] === false
                ? putenv($key)
                : putenv($key.'='.$values['process']);

            $this->restoreEnvironmentValue($_ENV, $key, $values['env']);
            $this->restoreEnvironmentValue($_SERVER, $key, $values['server']);
        }

        @unlink($this->envPath);
        @rmdir($this->temporaryDirectory);

        parent::tearDown();
    }

    public function test_it_writes_the_env_file_atomically_and_preserves_permissions(): void
    {
        [$result] = $this->persistEnvValues(new InstallCommand, [
            'ATOMIC_EXISTING_KEY' => 'updated',
            'ATOMIC_TEST_KEY' => 'saved value',
        ]);

        clearstatcache(true, $this->envPath);

        $this->assertTrue($result);
        $this->assertSame(
            "APP_ENV=install\nATOMIC_EXISTING_KEY=\"updated\"\n\nATOMIC_TEST_KEY=\"saved value\"",
            file_get_contents($this->envPath),
        );
        $this->assertSame(0640, fileperms($this->envPath) & 0777);
        $this->assertSame([], glob($this->temporaryDirectory.'/.env.tmp.*'));
    }

    public function test_failed_temporary_write_preserves_original_and_removes_temporary_file(): void
    {
        $original = file_get_contents($this->envPath);
        $command = new class extends InstallCommand
        {
            public ?string $temporaryPath = null;

            protected function createEnvTemporaryFile(string $directory): string|false
            {
                $this->temporaryPath = parent::createEnvTemporaryFile($directory);

                return $this->temporaryPath;
            }

            protected function writeEnvTemporaryFile(string $path, string $contents): int|false
            {
                return false;
            }
        };

        [$result, $output] = $this->persistEnvValues($command, ['APP_ENV' => 'store']);

        $this->assertFalse($result);
        $this->assertStringContainsString('Could not write the temporary .env file.', $output);
        $this->assertSame($original, file_get_contents($this->envPath));
        $this->assertNotNull($command->temporaryPath);
        $this->assertFileDoesNotExist($command->temporaryPath);
    }

    public function test_failed_rename_preserves_original_and_removes_temporary_file(): void
    {
        $original = file_get_contents($this->envPath);
        $command = new class extends InstallCommand
        {
            public ?string $temporaryPath = null;

            protected function createEnvTemporaryFile(string $directory): string|false
            {
                $this->temporaryPath = parent::createEnvTemporaryFile($directory);

                return $this->temporaryPath;
            }

            protected function replaceEnvFile(string $temporaryPath, string $envPath): bool
            {
                return false;
            }
        };

        [$result, $output] = $this->persistEnvValues($command, ['APP_ENV' => 'store']);

        $this->assertFalse($result);
        $this->assertStringContainsString('Could not replace the .env file.', $output);
        $this->assertSame($original, file_get_contents($this->envPath));
        $this->assertNotNull($command->temporaryPath);
        $this->assertFileDoesNotExist($command->temporaryPath);
    }

    #[DataProvider('dotenvValues')]
    public function test_special_values_survive_a_dotenv_round_trip(string $key, string $value): void
    {
        [$result] = $this->persistEnvValues(new InstallCommand, [$key => $value]);

        $loaded = Dotenv::createArrayBacked($this->temporaryDirectory)->load();

        $this->assertTrue($result);
        $this->assertArrayHasKey($key, $loaded);
        $this->assertSame(strlen($value), strlen((string) $loaded[$key]));
        $this->assertSame(hash('sha256', $value), hash('sha256', (string) $loaded[$key]));
    }

    /** @return array<string, array{string, string}> */
    public static function dotenvValues(): array
    {
        return [
            'password containing hash' => ['DB_PASSWORD', 'abc#123'],
            'username containing space' => ['DB_USERNAME', 'abc def'],
            'database containing double quote' => ['DB_DATABASE', 'abc"123'],
            'password containing single quote' => ['DB_PASSWORD', "abc'123"],
            'username containing backslash' => ['DB_USERNAME', 'abc\\123'],
            'password containing dollar' => ['DB_PASSWORD', 'abc$123'],
            'database containing equals' => ['DB_DATABASE', 'abc=123'],
            'password containing spaced hash' => ['DB_PASSWORD', 'abc # 123'],
            'username containing tab' => ['DB_USERNAME', "abc\t123"],
            'password containing line feed' => ['DB_PASSWORD', "abc\n123"],
            'database containing carriage return' => ['DB_DATABASE', "abc\r123"],
            'password containing combined characters' => ['DB_PASSWORD', "a b#c\"d'e\\f\$g=h\ti\nj"],
        ];
    }

    /**
     * @param  array<string, string>  $values
     * @return array{bool, string}
     */
    private function persistEnvValues(InstallCommand $command, array $values): array
    {
        $input = new ArrayInput([], $command->getDefinition());
        $buffer = new BufferedOutput;

        $inputProperty = new ReflectionProperty($command, 'input');
        $inputProperty->setValue($command, $input);

        $outputProperty = new ReflectionProperty($command, 'output');
        $outputProperty->setValue($command, new OutputStyle($input, $buffer));

        $method = new ReflectionMethod($command, 'persistEnvValues');

        return [$method->invoke($command, $values), $buffer->fetch()];
    }

    /** @param array<string, mixed> $environment */
    private function restoreEnvironmentValue(array &$environment, string $key, mixed $value): void
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
            'ATOMIC_TEST_KEY',
            'ATOMIC_EXISTING_KEY',
            'DB_PASSWORD',
            'DB_USERNAME',
            'DB_DATABASE',
        ];
    }
}
