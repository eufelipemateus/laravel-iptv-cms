<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\InstallCommand;
use Illuminate\Console\OutputStyle;
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
    }

    protected function tearDown(): void
    {
        $this->app->setBasePath($this->originalBasePath);
        putenv('ATOMIC_TEST_KEY');
        putenv('ATOMIC_EXISTING_KEY');
        unset(
            $_ENV['ATOMIC_TEST_KEY'],
            $_ENV['ATOMIC_EXISTING_KEY'],
            $_SERVER['ATOMIC_TEST_KEY'],
            $_SERVER['ATOMIC_EXISTING_KEY'],
        );

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
            "APP_ENV=install\nATOMIC_EXISTING_KEY=updated\n\nATOMIC_TEST_KEY=\"saved value\"",
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
}
