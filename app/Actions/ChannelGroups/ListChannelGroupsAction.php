<?php

namespace App\Actions\ChannelGroups;

use App\Models\ChannelGroup;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListChannelGroupsAction
{
    use AsAction;

    public function handle(int $perPage = 25): LengthAwarePaginator
    {
        return ChannelGroup::query()
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
