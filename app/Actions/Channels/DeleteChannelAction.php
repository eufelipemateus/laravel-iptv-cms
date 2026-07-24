<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteChannelAction
{
    use AsAction;

    public function handle(Channel $channel): void
    {
        $channel->delete();
    }
}
