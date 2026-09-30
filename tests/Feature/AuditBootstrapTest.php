<?php

namespace Tests\Feature;

use Symfony\Component\Process\Process;
use Tests\TestCase;

class AuditBootstrapTest extends TestCase
{
    public function test_package_discovery_does_not_require_a_database_connection_for_audit_observers(): void
    {
        $process = new Process(
            [PHP_BINARY, 'artisan', 'package:discover', '--ansi'],
            base_path(),
            [
                'APP_ENV' => 'testing',
                'DB_CONNECTION' => 'mysql',
                'DB_HOST' => '127.0.0.1',
                'DB_PORT' => '1',
                'DB_DATABASE' => 'unavailable',
                'DB_USERNAME' => 'unavailable',
                'DB_PASSWORD' => 'unavailable',
            ],
        );
        $process->setTimeout(30);
        $process->run();

        $this->assertSame(0, $process->getExitCode(), $process->getErrorOutput().$process->getOutput());
    }
}
