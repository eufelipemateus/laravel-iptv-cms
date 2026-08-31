<?php

namespace App\Http\Requests;

use App\Enums\OperationMode;
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
            'mode' => ['required', Rule::enum(OperationMode::class)],
            'confirm_mode_change' => ['nullable', 'boolean'],
        ];
    }
}
