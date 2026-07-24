<?php

namespace App\Actions\ChannelUrls;

use App\Models\ChannelUrl;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreChannelUrlAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ChannelUrl
    {
        return ChannelUrl::create($data);
    }
}
