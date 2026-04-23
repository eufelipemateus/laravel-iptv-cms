<?php

namespace App\Dashs;

use App\Helpers\IPTVDashBase;
use App\Models\ChannelGroup;


class ChannelGroups extends IPTVDashBase {
    public static  $title = "Groups Total";

    public static function view(){
        $data['total'] = ChannelGroup::count();
        return view('channel_group_dash', $data);
    }
}
