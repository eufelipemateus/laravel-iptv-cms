<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\ChannelGroup;


class ChannelGroups extends DashBase {
    public static  $title = "Groups Total";

    public static function view(){
        $data['total'] = ChannelGroup::count();
        return view('channel_group_dash', $data);
    }
}
