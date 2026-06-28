<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\ChannelCdn;

class ChannelCdns extends DashBase {
    public static  $title = "CDNs Total";

    public static function view(){
        $data['total'] = ChannelCdn::count();
        return view('channel_cdn_dash', $data);
    }
}
