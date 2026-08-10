<?php

namespace Tests\Feature\Commands;

use Tests\TestCase;

class ResetStoreDemoDataCommandTest extends TestCase
{
    public function test_command_fails_outside_store_environment(): void
    {
        config()->set('app.env', 'testing');

        $this->artisan('store:reset-demo-data')
            ->expectsOutput('Command is allowed only when APP_ENV=store.')
            ->assertExitCode(1);
    }

    public function test_command_runs_in_store_environment(): void
    {
        config()->set('app.env', 'store');

        $this->artisan('store:reset-demo-data')
            ->expectsOutput('Starting store demo reset.')
            ->expectsOutput('Store demo reset completed successfully.')
            ->assertExitCode(0);
    }
}
