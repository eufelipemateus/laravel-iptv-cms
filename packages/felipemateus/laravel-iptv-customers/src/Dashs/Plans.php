<?php

namespace FelipeMateus\IPTVCustomers\Dashs;

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;
use FelipeMateus\IPTVCustomers\Models\IPTVPlan;

class Plans extends IPTVDashBase {
    public static  $title = "Plans Total";

    public static function view(){
        $data['total'] = IPTVPlan::count();
        return view('IPTV::plan_dash', $data);
    }
}
