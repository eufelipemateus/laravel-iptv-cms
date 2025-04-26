<?php

namespace  FelipeMateus\IPTVCustomers\Controllers;

use Illuminate\Http\Request;
use FelipeMateus\IPTVCustomers\Models\IPTVPlan;
use FelipeMateus\IPTVCustomers\Models\IPTVPlanGroup;
use FelipeMateus\IPTVCore\Controllers\CoreController;

class PlanGroupController extends CoreController
{
    //

    function add($plan_id, Request $request){

        $this->validate($request, [
			'iptv_group_id' => 'string|required|exists:iptv_channel_groups,id',
        ]);

        $plan =  IPTVPlan::findOrFail($plan_id);
        $group_id =  $request->input('iptv_group_id');

	    $plan->groups()->save( IPTVPlanGroup::findOrFail($group_id));
        return redirect()->route('show_plan',['id'=>$plan->id]);
    }


    function delete($plan_id, Request $request){
        $this->validate($request, [
			'iptv_group_id' => 'string|required|exists:iptv_channel_groups,id',
        ]);

        $plan =  IPTVPlan::findOrFail($plan_id);
        $group_id = $request->input('iptv_group_id');
        $iptv_group = IPTVPlanGroup::findOrFail($group_id)->plan()->dissociate();
        $iptv_group->save();
        return redirect()->route('show_plan',['id'=>$plan->id]);
    }
}
