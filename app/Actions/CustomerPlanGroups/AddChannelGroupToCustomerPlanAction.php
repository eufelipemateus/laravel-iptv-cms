<?php

namespace App\Actions\CustomerPlanGroups;

use App\Models\ChannelGroup;
use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class AddChannelGroupToCustomerPlanAction
{
    use AsAction;

    public function handle(CustomerPlan $plan, int $groupId): CustomerPlan
    {
        $plan->groups()->save(ChannelGroup::findOrFail($groupId));

        return $plan;
    }
}
