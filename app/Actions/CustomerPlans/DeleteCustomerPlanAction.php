<?php

namespace App\Actions\CustomerPlans;

use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteCustomerPlanAction
{
    use AsAction;

    public function handle(CustomerPlan $plan): void
    {
        $plan->delete();
    }
}
