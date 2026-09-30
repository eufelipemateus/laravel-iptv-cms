<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\IPTVVodVideo;

class Vods extends DashBase
{
    public static $title = 'Videos';

    public static function view()
    {
        return view('vod_dash', [
            'total' => IPTVVodVideo::count(),
            'storage' => IPTVVodVideo::sum('file_size'),
        ]);
    }
}
