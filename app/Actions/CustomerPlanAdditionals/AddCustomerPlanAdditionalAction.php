<?php

namespace App\Actions\CustomerPlanAdditionals;

use App\Models\Customer;
use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class AddCustomerPlanAdditionalAction
{
    use AsAction;

    public function handle(Customer $customer, int $planId): Customer
    {
        $plan = CustomerPlan::where('additional', true)->findOrFail($planId);

        $customer->plans_additional()->syncWithoutDetaching([$plan->id]);

        return $customer;
    }
}
