<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        if ($this->filled('regenerate')) {
            return [
                'regenerate' => ['required', 'string'],
            ];
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('iptv_customers', 'username')->ignore($this->route('id')),
            ],
            'iptv_plan_id' => ['required', 'integer', 'exists:iptv_plans,id'],
            'iptv_cdn_id' => ['nullable', 'integer', 'exists:iptv_cdns,id'],
            'due_day' => ['nullable', 'integer', 'in:5,10,15,20,25'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
        ];
    }
}
