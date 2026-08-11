<?php

namespace App\Actions\ChannelCdns;

use App\Models\ChannelCdn;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChannelCdnAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ChannelCdn $cdn, array $data): ChannelCdn
    {
        $cdn->update($data);

        return $cdn;
    }
}
