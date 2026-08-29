<?php

namespace App\Actions\Customers;

use App\Models\Channel;
use App\Models\Customer;
use App\Models\IPTVConfig;
use App\Models\IPTVVodVideo;
use Lorisleiva\Actions\Concerns\AsAction;

class GetCustomerPlaylistDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(Customer $customer, string $slug): array
    {
        abort_unless($customer->cdn?->slug === $slug, 404);

        return [
            'list' => Channel::getCustomerChannelListM3u8($slug, $customer->getKey()),
            'vods' => config('modules.vod.enabled', false)
                ? IPTVVodVideo::withVideo()->orderBy('name')->get()
                : [],
            'download' => IPTVConfig::get('DOWNLOAD_FILE'),
            'epg_url' => config('modules.epg.enabled', true)
                ? route('epg.customer', ['slug' => $slug])
                : null,
        ];
    }
}
