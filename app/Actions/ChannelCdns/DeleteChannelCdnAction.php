<?php

namespace App\Actions\ChannelCdns;

use App\Models\ChannelCdn;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteChannelCdnAction
{
    use AsAction;

    public function handle(ChannelCdn $cdn): void
    {
        $cdn->delete();
    }
}
