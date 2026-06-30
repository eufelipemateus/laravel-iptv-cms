<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChannelListM3URequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->route('slug'),
        ]);
    }

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
            'slug' => ['required', 'string', 'exists:iptv_cdns,slug'],
        ];
    }

    public function slug(): string
    {
        return (string) $this->input('slug');
    }
}
