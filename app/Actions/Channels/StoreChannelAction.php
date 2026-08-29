<?php

namespace App\Actions\Channels;

use App\Models\Channel;
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

        return $channel;
    }
}
