<?php

namespace App\Actions\CustomerPlanGroups;

use App\Models\ChannelGroup;
use App\Models\CustomerPlan;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class AddChannelGroupToCustomerPlanAction
{
    use AsAction;

    public function handle(CustomerPlan $plan, int $groupId): CustomerPlan
    {
        $group = ChannelGroup::findOrFail($groupId);

        if ($group->iptv_plan_id !== null && (int) $group->iptv_plan_id !== (int) $plan->id) {
            throw ValidationException::withMessages([
                'iptv_group_id' => 'The channel group already belongs to another plan.',
            ]);
        }

        $plan->groups()->save($group);

        return $plan;
    }
}
