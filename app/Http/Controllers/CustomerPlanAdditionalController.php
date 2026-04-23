<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\CustomerPlan;
use App\Models\Customer;

class CustomerPlanAdditionalController extends Controller
{

    public function add($customer_id, Request $request){

        $this->validate($request, [
			'iptv_plan_id' => [
                'string',
                'required',
                Rule::exists('iptv_plans', 'id')->where(function ($query) {
                    return $query->where('additional', 1);
                }),
            ]
        ]);


        $customer = Customer::findOrFail($customer_id);
        $plan_id  = $request->input('iptv_plan_id');

        $customer->plans_additional()->save( CustomerPlan::findOrFail($plan_id));
        return redirect()->route('show_customer',['id'=>$customer->id]);
    }

    public function del($customer_id, Request $request){
        $this->validate($request, [
			'iptv_plan_id' => [
                'string',
                'required',
                Rule::exists('iptv_plans', 'id')->where(function ($query) {
                    return $query->where('additional', 1);
                }),
            ]
        ]);

        $customer = Customer::findOrFail($customer_id);
        $plan_id  = $request->input('iptv_plan_id');

        $customer->plans_additional()->detach($plan_id);
        $customer->save();

        return redirect()->route('show_customer',['id'=>$customer->id]);

    }
}
