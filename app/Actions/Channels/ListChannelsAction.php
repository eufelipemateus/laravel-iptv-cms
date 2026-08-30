<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListChannelsAction
{
    use AsAction;

    public function handle(int $perPage = 25): LengthAwarePaginator
    {
        return Channel::query()
            ->with('group')
            ->orderBy('radio')
            ->orderBy('number')
            ->paginate($perPage)
            ->withQueryString();
    }
}
