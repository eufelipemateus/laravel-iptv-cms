<?php

namespace App\Actions\ChannelCdns;

use App\Models\IPTVConfig;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChannelCdnListDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(): array
    {
        return [
            'list' => ListChannelCdnsAction::run(),
            'url_cdn' => IPTVConfig::get('URL_CDN'),
            'donwload' => IPTVConfig::get('DOWNLOAD_FILE'),
        ];
    }
}
