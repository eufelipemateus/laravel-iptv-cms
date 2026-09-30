<?php

namespace App\Jobs;

use App\Models\EpgSource;
use App\Services\Epg\EpgSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncEpgSource implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout;

    public int $tries = 3;

    public function __construct(public EpgSource $source)
    {
        $this->timeout = (int) config('modules.epg.job_timeout', 1800);
        $this->onConnection((string) config('modules.epg.queue_connection', 'database'));
        $this->onQueue((string) config('modules.epg.queue', 'epg'));
    }

    /** @return list<int> */
    public function backoff(): array
    {
        return [60, 300, 900];
    }

    public function uniqueId(): string
    {
        return (string) $this->source->getKey();
    }

    public function uniqueFor(): int
    {
        return (int) config('modules.epg.sync_lock_seconds', 3600);
    }

    public function handle(EpgSyncService $sync): void
    {
        if (config('modules.epg.enabled', false) && $this->source->enabled) {
            $sync->sync($this->source);
        }
    }
}
