<?php

namespace App\Actions\ChannelUrls;

use App\Models\ChannelUrl;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteChannelUrlAction
{
    use AsAction;

    public function handle(ChannelUrl $url): void
    {
        $url->delete();
    }
}
