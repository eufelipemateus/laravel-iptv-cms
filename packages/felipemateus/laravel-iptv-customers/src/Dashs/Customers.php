<?php

namespace FelipeMateus\IPTVCustomers\Dashs;

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomer;

class Customers extends IPTVDashBase {
    public static  $title = "Customers Total";

    public static function view(){
        $data['total'] = IPTVCustomer::count();
        return view('IPTV::customer_dash', $data);
    }
}
