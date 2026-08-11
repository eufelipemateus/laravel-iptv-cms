<?php

namespace App\Http\Controllers;

use App\Actions\CustomerPlanGroups\AddChannelGroupToCustomerPlanAction;
use App\Actions\CustomerPlanGroups\RemoveChannelGroupFromCustomerPlanAction;
use App\Http\Requests\CustomerPlanGroupRequest;
use App\Models\CustomerPlan;
use Illuminate\Http\RedirectResponse;

class CustomerPlanGroupController extends Controller
{
    //

    public function add(CustomerPlanGroupRequest $request): RedirectResponse
    {
        $plan = CustomerPlan::findOrFail($request->planId());
        $data = $request->validated();

        AddChannelGroupToCustomerPlanAction::run($plan, (int) $data['iptv_group_id']);

        return redirect()->route('show_customer_plan', ['id' => $plan->id]);
    }

    public function delete(CustomerPlanGroupRequest $request): RedirectResponse
    {
        $plan = CustomerPlan::findOrFail($request->planId());
        $data = $request->validated();

        RemoveChannelGroupFromCustomerPlanAction::run($plan, (int) $data['iptv_group_id']);

        return redirect()->route('show_customer_plan', ['id' => $plan->id]);
    }
}
