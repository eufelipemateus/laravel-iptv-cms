<?php

namespace App\Http\Controllers;

use App\Actions\CustomerPlanAdditionals\AddCustomerPlanAdditionalAction;
use App\Actions\CustomerPlanAdditionals\RemoveCustomerPlanAdditionalAction;
use App\Http\Requests\CustomerPlanAdditionalRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CustomerPlanAdditionalController extends Controller
{
    public function add($customer_id, CustomerPlanAdditionalRequest $request): RedirectResponse
    {
        $customer = Customer::findOrFail($customer_id);
        $data = $request->validated();

        AddCustomerPlanAdditionalAction::run($customer, (int) $data['iptv_plan_id']);

        return redirect()->route('show_customer', ['id' => $customer->id]);
    }

    public function del($customer_id, CustomerPlanAdditionalRequest $request): RedirectResponse
    {
        $customer = Customer::findOrFail($customer_id);
        $data = $request->validated();

        RemoveCustomerPlanAdditionalAction::run($customer, (int) $data['iptv_plan_id']);

        return redirect()->route('show_customer', ['id' => $customer->id]);
    }
}
