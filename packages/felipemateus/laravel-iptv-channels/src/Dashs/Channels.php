<?php

namespace FelipeMateus\IPTVChannels\Dashs;

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;
use FelipeMateus\IPTVChannels\Model\IPTVChannel;


class Channels extends IPTVDashBase {
    public static  $title = "Channels Total";

    public static function view(){
        $data['total'] = IPTVChannel::count();
        return view('IPTV::channel_dash', $data);
    }
}
