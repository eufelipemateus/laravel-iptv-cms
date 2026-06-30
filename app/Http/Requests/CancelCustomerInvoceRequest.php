<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CancelCustomerInvoceRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->route('customer_id'),
            'id' => $this->route('id'),
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
            'customer_id' => ['required', 'integer', 'exists:iptv_customers,id'],
            'id' => [
                'required',
                'integer',
                Rule::exists('iptv_customer_invoces', 'id')->where(
                    fn ($query) => $query->where('iptv_customer_id', $this->route('customer_id')),
                ),
            ],
        ];
    }

    public function customerId(): int
    {
        return $this->integer('customer_id');
    }

    public function invoceId(): int
    {
        return $this->integer('id');
    }
}
