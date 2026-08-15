<?php

namespace App\Console\Commands;

use App\Models\EpgSource;
use App\Services\Epg\EpgSyncService;
use Illuminate\Console\Command;
use Throwable;

class SyncEpg extends Command
{
    protected $signature = 'epg:sync {source? : Source ID}';

    protected $description = 'Synchronize enabled XMLTV EPG sources';

    public function handle(EpgSyncService $sync): int
    {
        if (! config('modules.epg.enabled', true)) {
            $this->warn('The EPG module is disabled.');

            return self::SUCCESS;
        }
        $query = EpgSource::query()->where('enabled', true);
        if ($this->argument('source') !== null) {
            $query->whereKey($this->argument('source'));
        }
        $sources = $query->get();
        if ($sources->isEmpty()) {
            $this->error('No enabled EPG source was found.');

            return self::FAILURE;
        }
        $failed = false;
        foreach ($sources as $source) {
            try {
                $result = $sync->sync($source);
                $this->info("{$source->name}: {$result['channels']} channels, {$result['programmes']} programmes.");
            } catch (Throwable $exception) {
                $failed = true;
                $this->error($source->name.': '.$exception->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
