<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\EpgSource;
use App\Models\IPTVConfig;
use Lorisleiva\Actions\Concerns\AsAction;

class GetChannelFormDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(?Channel $channel = null): array
    {
        $data = [
            'Groupslist' => ChannelGroup::all(),
            'radio_stream' => IPTVConfig::get('RADIO_STREAM'),
            'EpgSources' => config('modules.epg.enabled', true)
                ? EpgSource::query()->where('enabled', true)->orderBy('name')->get()
                : collect(),
        ];

        if ($channel) {
            $data += [
                'Channel' => $channel->load('epgChannel.source'),
                'Cdnslist' => ChannelCdn::all(),
                'urls' => ChannelUrl::where('iptv_channel_id', $channel->getKey())->get(),
            ];
        }

        return $data;
    }
}
