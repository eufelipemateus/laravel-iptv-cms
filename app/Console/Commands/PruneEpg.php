<?php

namespace App\Console\Commands;

use App\Models\EpgProgramme;
use Illuminate\Console\Command;

class PruneEpg extends Command
{
    protected $signature = 'epg:prune';

    protected $description = 'Remove expired EPG programmes';

    public function handle(): int
    {
        if (! config('modules.epg.enabled', true)) {
            return self::SUCCESS;
        }
        $deleted = EpgProgramme::where('end_at', '<', now()->subDays((int) config('modules.epg.retention_days', 7)))->delete();
        $this->info("Deleted {$deleted} expired programmes.");

        return self::SUCCESS;
    }
}
