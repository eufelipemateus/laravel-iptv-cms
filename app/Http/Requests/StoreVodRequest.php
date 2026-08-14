<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVodRequest extends FormRequest
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
                'required',
                'file',
                'max:'.config('vod.max_upload_kilobytes', 10485760),
                'mimetypes:'.implode(',', config('vod.allowed_video_mimetypes', [])),
            ],
        ];
    }
}
