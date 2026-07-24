<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class IPTVCustomerInvoceCreateInvoceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->route('customer_id'),
        ]);
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'customer_id' => 'required|integer|exists:iptv_customers,id',
            'duedate_at' => 'required|date',
            'payeddate_at' => 'nullable|date',
        ];
    }

    public function customerId(): int
    {
        return $this->integer('customer_id');
    }
}
