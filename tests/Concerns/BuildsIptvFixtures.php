<?php

namespace Tests\Concerns;

use App\Enums\OperationMode;
use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Models\IPTVConfig;
use App\Services\OperationModeService;

trait BuildsIptvFixtures
{
    protected function setOperationMode(OperationMode $mode): void
    {
        IPTVConfig::set('mode', $mode->value, 'string');
        app(OperationModeService::class)->forgetCachedMode();
    }

    protected function enablePublicCdn(bool|string $value = true): void
    {
        IPTVConfig::set('URL_CDN', $value, 'bool');
    }

    protected function enablePlaylistDownload(bool|string $value = true): void
    {
        IPTVConfig::set('DOWNLOAD_FILE', $value, 'bool');
    }

    protected function makePlayableChannel(
        ChannelCdn $cdn,
        ?CustomerPlan $plan = null,
        array $channelAttributes = [],
        array $urlAttributes = [],
    ): Channel {
        $group = ChannelGroup::factory()->create([
            'iptv_plan_id' => $plan?->id,
        ]);

        $channel = Channel::factory()->create(array_merge([
            'group_id' => $group->id,
        ], $channelAttributes));

        ChannelUrl::factory()->create(array_merge([
            'iptv_channel_id' => $channel->id,
            'iptv_cdn_id' => $cdn->id,
        ], $urlAttributes));

        return $channel;
    }

    protected function makeCustomerWithPlanAndCdn(?CustomerPlan $plan = null, ?ChannelCdn $cdn = null): Customer
    {
        return Customer::factory()->active()->create([
            'iptv_plan_id' => $plan?->id ?? CustomerPlan::factory()->active(),
            'iptv_cdn_id' => $cdn?->id ?? ChannelCdn::factory(),
        ]);
    }

    protected function playlistLines(string $playlist): array
    {
        return array_values(array_filter(
            array_map('trim', preg_split('/\R/', $playlist) ?: []),
            fn (string $line) => $line !== '',
        ));
    }
}
