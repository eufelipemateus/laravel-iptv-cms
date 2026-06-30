<?php

namespace App\Actions\CustomerPlans;

use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCustomerPlanAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CustomerPlan $plan, array $data, bool $isActive, bool $isAdditional): CustomerPlan
    {
        $data['active'] = $isActive;
        $data['additional'] = $isAdditional;

        $plan->update($data);

        return $plan;
    }
}
