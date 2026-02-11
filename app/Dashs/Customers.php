<?php

namespace App\Dashs;

use App\Helpers\IPTVDashBase;
use App\Models\Customer;

class Customers extends IPTVDashBase {
    public static  $title = "Customers Total";

    public static function view(){
        $data['total'] = Customer::count();
        return view('customer_dash', $data);
    }
}
