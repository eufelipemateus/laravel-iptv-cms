<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use App\Models\IPTVConfig;
use App\Models\IPTVVodVideo;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPublicPlaylistDataAction
{
    use AsAction;

    /** @return array<string, mixed> */
    public function handle(string $slug): array
    {
        return [
            'list' => Channel::getListM3u8($slug),
            'vods' => config('modules.vod.enabled', false)
                ? IPTVVodVideo::withVideo()->orderBy('name')->get()
                : [],
            'download' => IPTVConfig::get('DOWNLOAD_FILE'),
        ];
    }
}
