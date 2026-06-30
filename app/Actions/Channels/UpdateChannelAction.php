<?php

namespace App\Actions\Channels;

use App\Models\Channel;
use Illuminate\Http\UploadedFile;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateChannelAction
{
    use AsAction;

    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Channel $channel, array $data, ?UploadedFile $image, bool $isRadio): Channel
    {
        unset($data['image']);

        $data['radio'] = $isRadio;
        $channel->update($data);

        if ($image !== null) {
            $channel->logo = $image;
            $channel->save();
        }

        return $channel;
    }
}
