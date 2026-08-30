<?php

namespace App\Http\Requests;

use App\Rules\ValidEpgUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreEpgSourceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'url' => ['required', new ValidEpgUrl],
            'format' => ['required', Rule::in(['xmltv'])],
            'timezone' => ['required', 'timezone'],
            'refresh_interval' => ['required', 'integer', 'min:5', 'max:43200'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }
}
