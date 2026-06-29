<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChannelUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'iptv_cdn_id' => ['required', 'integer', 'exists:iptv_cdns,id'],
            'iptv_channel_id' => ['required', 'integer', 'exists:iptv_channels,id'],
            'url_stream' => ['required', 'string'],
        ];
    }
}
