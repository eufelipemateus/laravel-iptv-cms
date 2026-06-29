<?php

namespace App\Dashs;

use App\Helpers\DashBase;
use App\Models\CustomerPlan ;

class CustomerPlans extends DashBase {
    public static  $title = "Plans Total";

    public static function view(){
        $data['total'] = CustomerPlan::count();
        return view('customer_plan_dash', $data);
    }
}
