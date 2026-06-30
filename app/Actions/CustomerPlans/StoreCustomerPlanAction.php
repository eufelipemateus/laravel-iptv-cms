<?php

namespace App\Actions\CustomerPlans;

use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreCustomerPlanAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, bool $isActive, bool $isAdditional): CustomerPlan
    {
        $data['active'] = $isActive;
        $data['additional'] = $isAdditional;

        return CustomerPlan::create($data);
    }
}
