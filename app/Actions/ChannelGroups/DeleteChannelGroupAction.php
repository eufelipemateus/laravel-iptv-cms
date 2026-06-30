<?php

namespace App\Actions\ChannelGroups;

use App\Models\ChannelGroup;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteChannelGroupAction
{
    use AsAction;

    public function handle(ChannelGroup $group): void
    {
        $group->delete();
    }
}
