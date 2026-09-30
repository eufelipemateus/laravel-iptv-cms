<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;

class Epg extends DashBase
{
    public static $title = 'EPG';

    public static function view()
    {
        return view('epg_dash', [
            'sources' => EpgSource::count(),
            'channels' => EpgChannel::count(),
            'programmes' => EpgProgramme::count(),
            'lastSync' => EpgSource::max('last_success_at'),
        ]);
    }
}
