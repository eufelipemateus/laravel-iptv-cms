<?php

namespace App\Actions\CustomerPlanAdditionals;

use App\Models\Customer;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveCustomerPlanAdditionalAction
{
    use AsAction;

    public function handle(Customer $customer, int $planId): Customer
    {
        $customer->plans_additional()->detach($planId);
        $customer->save();

        return $customer;
    }
}
