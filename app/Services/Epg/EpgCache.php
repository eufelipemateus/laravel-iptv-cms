<?php

namespace App\Services\Epg;

use Illuminate\Support\Facades\Cache;

class EpgCache
{
    public function invalidate(): void
    {
        Cache::increment('epg:xmltv:version');
    }
}
