<?php

namespace App\Actions\ChannelCdns;

use App\Models\ChannelCdn;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreChannelCdnAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ChannelCdn
    {
        return ChannelCdn::create($data);
    }
}
