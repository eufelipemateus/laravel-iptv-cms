<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'file' => [
                'nullable',
                'file',
                'max:'.config('vod.max_upload_kilobytes', 10485760),
                'mimetypes:'.implode(',', config('vod.allowed_video_mimetypes', [])),
            ],
        ];
    }
}
