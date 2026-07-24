<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPlanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->input('iptv_tax_vat_id') === 'null') {
            $this->merge(['iptv_tax_vat_id' => null]);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'iptv_tax_vat_id' => ['nullable', 'integer', 'exists:iptv_tax_vat,id'],
            'active' => ['sometimes', 'boolean'],
            'additional' => ['sometimes', 'boolean'],
        ];
    }
}
