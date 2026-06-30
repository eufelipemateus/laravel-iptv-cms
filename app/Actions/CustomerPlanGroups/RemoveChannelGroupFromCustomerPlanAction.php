<?php

namespace App\Actions\CustomerPlanGroups;

use App\Models\ChannelGroup;
use App\Models\CustomerPlan;
use Lorisleiva\Actions\Concerns\AsAction;

class RemoveChannelGroupFromCustomerPlanAction
{
    use AsAction;

    public function handle(CustomerPlan $plan, int $groupId): CustomerPlan
    {
        $group = ChannelGroup::findOrFail($groupId)->plan()->dissociate();
        $group->save();

        return $plan;
    }
}
