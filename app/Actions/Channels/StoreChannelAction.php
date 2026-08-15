<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use App\Services\Epg\EpgCache;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;

class StoreChannelAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, UploadedFile $image, bool $isRadio): Channel
    {
        unset($data['image']);

        $data['radio'] = $isRadio;

        $channel = Channel::create($data);
        $channel->logo = $image;
        $channel->save();

        if ($channel->epg_channel_id) {
            app(EpgCache::class)->invalidate();
        }

        return $channel;
    }
}
