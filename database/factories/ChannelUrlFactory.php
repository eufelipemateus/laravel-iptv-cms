<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelUrl;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelUrlFactory extends Factory
{
    protected $model = ChannelUrl::class;

    public function definition(): array
    {
        return [
            'iptv_cdn_id' => ChannelCdn::factory(),
            'iptv_channel_id' => Channel::factory(),
            'url_stream' => $this->faker->unique()->url().'/stream.m3u8',
        ];
    }
}
