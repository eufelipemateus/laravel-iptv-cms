<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChannelRequest extends FormRequest
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
            'number' => ['required', 'numeric', 'unique:iptv_channels,number'],
            'name' => ['required', 'string', 'max:60'],
            'image' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:2048'],
            'group_id' => ['required', 'integer', 'exists:iptv_channel_groups,id'],
            'radio' => ['sometimes', 'boolean'],
        ];
    }
}
