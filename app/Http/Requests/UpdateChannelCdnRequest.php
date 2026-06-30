<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateChannelCdnRequest extends FormRequest
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
            'slug' => [
                'required',
                'string',
                'max:50',
                Rule::unique('iptv_cdns', 'slug')->ignore($this->route('id')),
            ],
            'name' => ['required', 'string', 'max:90'],
        ];
    }
}
