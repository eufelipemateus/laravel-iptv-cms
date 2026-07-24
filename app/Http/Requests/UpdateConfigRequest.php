<?php

namespace App\Http\Requests;

use App\Helpers\Locale;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateConfigRequest extends FormRequest
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
            'CURRENT_LOCALE' => ['required', 'string', Rule::in(array_keys(Locale::getList()))],
        ];
    }
}
