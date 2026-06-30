<?php

namespace App\Actions\ChannelGroups;

use App\Models\ChannelGroup;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChannelGroupAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ChannelGroup $group, array $data): ChannelGroup
    {
        $group->update($data);

        return $group;
    }
}
