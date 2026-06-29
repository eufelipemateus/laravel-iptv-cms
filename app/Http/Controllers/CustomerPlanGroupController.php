<?php

namespace  App\Http\Controllers;

use App\Models\ChannelGroup;
use App\Models\CustomerPlan;
use Illuminate\Http\Request;


class CustomerPlanGroupController extends Controller
{
    //

    function add($plan_id, Request $request){

        $this->validate($request, [
			'iptv_group_id' => 'string|required|exists:iptv_channel_groups,id',
        ]);

        $plan =  CustomerPlan::findOrFail($plan_id);
        $group_id =  $request->input('iptv_group_id');

	    $plan->groups()->save( ChannelGroup::findOrFail($group_id));
        return redirect()->route('show_customer_plan',['id'=>$plan->id]);
    }


    function delete($plan_id, Request $request){
        $this->validate($request, [
			'iptv_group_id' => 'string|required|exists:iptv_channel_groups,id',
        ]);

        $plan =  CustomerPlan::findOrFail($plan_id);
        $group_id = $request->input('iptv_group_id');
        $iptv_group = ChannelGroup::findOrFail($group_id)->plan()->dissociate();
        $iptv_group->save();
        return redirect()->route('show_customer_plan',['id'=>$plan->id]);
    }
}
