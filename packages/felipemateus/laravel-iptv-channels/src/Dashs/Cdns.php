<?php

namespace FelipeMateus\IPTVChannels\Dashs;

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;
use FelipeMateus\IPTVChannels\Model\IPTVCdn;

class Cdns extends IPTVDashBase {
    public static  $title = "CDNs Total";

    public static function view(){
        $data['total'] = IPTVCdn::count();
        return view('IPTV::cdn_dash', $data);
    }
}
