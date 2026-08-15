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

    public function add(CustomerPlan $customerPlan, CustomerPlanGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();


        AddChannelGroupToCustomerPlanAction::run($customerPlan, (int) $data['iptv_group_id']);

        return redirect()->route('show_customer_plan', ['customerPlan' => $customerPlan]);
    }

    public function delete(CustomerPlan $customerPlan, CustomerPlanGroupRequest $request): RedirectResponse
    {
        $data = $request->validated();

        RemoveChannelGroupFromCustomerPlanAction::run($customerPlan, (int) $data['iptv_group_id']);

        return redirect()->route('show_customer_plan', ['customerPlan' => $customerPlan]);
    }
}
