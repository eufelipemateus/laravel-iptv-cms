<?php

namespace Tests\Unit\Commands;

use App\Console\Commands\InstallCommand;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use ReflectionMethod;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Tests\TestCase;

class InstallCommandTest extends TestCase
{
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

    /**
     * @return array{bool, string}
     */
    private function generateApplicationKey(): array
    {
        $buffer = new BufferedOutput;
        $command = new InstallCommand;

        $outputProperty = new ReflectionProperty($command, 'output');
        $outputProperty->setValue($command, new OutputStyle(new ArrayInput([]), $buffer));

        $method = new ReflectionMethod($command, 'generateApplicationKey');
        $result = $method->invoke($command);

        return [$result, $buffer->fetch()];
    }
}
