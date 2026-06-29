<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\Channel;


class Channels extends DashBase {
    public static  $title = "Channels Total";

    public static function view(){
        $data['total'] = Channel::count();
        return view('channel_dash', $data);
    }
}
