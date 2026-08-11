<?php

namespace App\Actions\CustomerPlanGroups;

use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveChannelGroupFromCustomerPlanAction
{
    use AsAction;

    public function handle(CustomerPlan $plan, int $groupId): CustomerPlan
    {
        $group = $plan->groups()->whereKey($groupId)->firstOrFail();
        $group->plan()->dissociate();
        $group->save();

        return $plan;
    }
}
