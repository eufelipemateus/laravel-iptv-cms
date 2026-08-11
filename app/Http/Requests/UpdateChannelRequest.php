<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChannelRequest extends FormRequest
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
            'number' => [
                'required',
                'numeric',
                Rule::unique('iptv_channels', 'number')->ignore($this->route('id')),
            ],
            'name' => ['required', 'string', 'max:60'],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,svg', 'max:2048'],
            'group_id' => ['required', 'integer', 'exists:iptv_channel_groups,id'],
            'radio' => ['sometimes', 'boolean'],
        ];
    }
}
