<?php

namespace App\Actions\ChannelUrls;

use App\Models\ChannelUrl;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChannelUrlAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ChannelUrl $url, array $data): ChannelUrl
    {
        $url->update($data);

        return $url;
    }
}
