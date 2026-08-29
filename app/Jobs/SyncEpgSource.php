<?php

namespace App\Jobs;

use App\Models\EpgSource;
use App\Services\Epg\EpgSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEpgSource implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public EpgSource $source) {}

    public function uniqueId(): string
    {
        return (string) $this->source->getKey();
    }

    public function uniqueFor(): int
    {
        return (int) config('modules.epg.sync_lock_seconds', 1800);
    }

    public function handle(EpgSyncService $sync): void
    {
        if (config('modules.epg.enabled', true) && $this->source->enabled) {
            $sync->sync($this->source);
        }
    }
}
