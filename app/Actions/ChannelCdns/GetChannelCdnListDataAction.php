<?php

namespace App\Actions\ChannelCdns;

use App\Models\IPTVConfig;
use App\Services\OperationModeService;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChannelCdnListDataAction
{
    use AsAction;

    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /** @return array<string, mixed> */
    public function handle(): array
    {
        return [
            'list' => ListChannelCdnsAction::run(),
            'url_cdn' => IPTVConfig::get('URL_CDN'),
            'donwload' => IPTVConfig::get('DOWNLOAD_FILE'),
            'show_m3u8_links' => $this->operationModeService->isM3u8(),
        ];
    }
}
