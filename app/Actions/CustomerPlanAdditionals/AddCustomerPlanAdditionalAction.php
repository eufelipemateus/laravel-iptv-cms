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
        $customer->plans_additional()->save(CustomerPlan::findOrFail($planId));

        return $customer;
    }
}
