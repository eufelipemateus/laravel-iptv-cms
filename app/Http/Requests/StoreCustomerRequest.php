<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:iptv_customers,username'],
            'iptv_plan_id' => ['required', 'integer', 'exists:iptv_plans,id'],
            'iptv_cdn_id' => ['nullable', 'integer', 'exists:iptv_cdns,id'],
            'due_day' => ['nullable', 'integer', 'in:5,10,15,20,25'],
            'industry' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:255'],
        ];
    }
}
