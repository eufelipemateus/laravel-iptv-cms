<?php

namespace App\Http\Controllers;

use App\Actions\CustomerPlanAdditionals\AddCustomerPlanAdditionalAction;
use App\Actions\CustomerPlanAdditionals\RemoveCustomerPlanAdditionalAction;
use App\Http\Requests\CustomerPlanAdditionalRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;

class CustomerPlanAdditionalController extends Controller
{
    public function add(Customer $customer, CustomerPlanAdditionalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        AddCustomerPlanAdditionalAction::run($customer, (int) $data['iptv_plan_id']);

        return redirect()->route('show_customer', ['customer' => $customer]);
    }

    public function del(Customer $customer, CustomerPlanAdditionalRequest $request): RedirectResponse
    {
        $data = $request->validated();

        RemoveCustomerPlanAdditionalAction::run($customer, (int) $data['iptv_plan_id']);

        return redirect()->route('show_customer', ['customer' => $customer]);
    }
}
