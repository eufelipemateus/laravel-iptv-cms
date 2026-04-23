<?php

namespace App\Dashs;

use App\Helpers\IPTVDashBase;
use App\Models\CustomerPlan ;

class CustomerPlans extends IPTVDashBase {
    public static  $title = "Plans Total";

    public static function view(){
        $data['total'] = CustomerPlan::count();
        return view('customer_plan_dash', $data);
    }
}
