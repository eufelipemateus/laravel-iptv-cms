<?php

namespace App\Actions\ChannelCdns;

use App\Models\ChannelCdn;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListChannelCdnsAction
{
    use AsAction;

    public function handle(int $perPage = 25): LengthAwarePaginator
    {
        return ChannelCdn::query()
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
