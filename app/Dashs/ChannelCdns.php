<?php

namespace App\Dashs;

use App\Helpers\IPTVDashBase;
use App\Models\ChannelCdn;

class ChannelCdns extends IPTVDashBase {
    public static  $title = "CDNs Total";

    public static function view(){
        $data['total'] = ChannelCdn::count();
        return view('channel_cdn_dash', $data);
    }
}
