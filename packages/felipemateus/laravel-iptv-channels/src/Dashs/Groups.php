<?php

namespace FelipeMateus\IPTVChannels\Dashs;

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;
use FelipeMateus\IPTVChannels\Model\IPTVChannelGroup;


class Groups extends IPTVDashBase {
    public static  $title = "Groups Total";

    public static function view(){
        $data['total'] = IPTVChannelGroup::count();
        return view('IPTV::group_dash', $data);
    }
}
