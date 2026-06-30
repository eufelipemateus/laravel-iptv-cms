<?php

namespace App\Actions\ChannelGroups;

use App\Models\ChannelGroup;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreChannelGroupAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ChannelGroup
    {
        return ChannelGroup::create($data);
    }
}
