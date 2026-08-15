<?php

namespace App\Console\Commands;

use App\Jobs\SyncEpgSource;
use App\Models\EpgSource;
use Illuminate\Console\Command;

class SyncDueEpg extends Command
{
    protected $signature = 'epg:sync-due';

    protected $description = 'Queue EPG sources whose refresh interval has elapsed';

    public function handle(): int
    {
        if (! config('modules.epg.enabled', true)) {
            return self::SUCCESS;
        }
        foreach (EpgSource::due() as $source) {
            SyncEpgSource::dispatch($source);
        }

        return self::SUCCESS;
    }
}
