<?php

namespace App\Jobs;

use App\Models\EpgSource;
use App\Services\Epg\EpgSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEpgSource implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public EpgSource $source) {}

    public function handle(EpgSyncService $sync): void
    {
        if (config('modules.epg.enabled', true) && $this->source->enabled) {
            $sync->sync($this->source);
        }
    }
}
