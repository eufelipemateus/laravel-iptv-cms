<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Throwable;

class ResetStoreDemoData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'store:reset-demo-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all data and restore baseline seed when app runs in store mode.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! app()->hasMacro('isStore') || ! app()->isStore()) {
            $this->error('Command is allowed only when APP_ENV=store.');

            return self::FAILURE;
        }

        $this->info('Starting store demo reset.');
        Log::warning('Store demo reset started.');

        $shouldToggleMaintenance = ! app()->runningUnitTests();

        if ($shouldToggleMaintenance) {
            Artisan::call('down', ['--render' => 'errors::503']);
        }

        try {
            $exitCode = Artisan::call('migrate:fresh', [
                '--seed' => true,
                '--force' => true,
            ]);

            $this->output->write(Artisan::output());

            if ($exitCode !== self::SUCCESS) {
                $this->error('Store demo reset failed while running migrate:fresh --seed.');
                Log::error('Store demo reset failed while running migrate:fresh --seed.');

                return self::FAILURE;
            }

            $this->info('Store demo reset completed successfully.');
            Log::warning('Store demo reset completed successfully.');

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Store demo reset failed with exception.', [
                'message' => $exception->getMessage(),
            ]);

            $this->error('Store demo reset failed with exception: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            if ($shouldToggleMaintenance) {
                Artisan::call('up');
            }
        }
    }
}
