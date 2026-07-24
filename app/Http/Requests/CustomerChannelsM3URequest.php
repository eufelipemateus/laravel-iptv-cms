<?php

namespace App\Http\Requests;

use App\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;

class CustomerChannelsM3URequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->route('slug'),
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
            'slug' => ['required', 'string', 'exists:iptv_cdns,slug'],
        ];
    }

    public function slug(): string
    {
        return (string) $this->input('slug');
    }

    public function customer(): Customer
    {
        $customer = $this->attributes->get('customer') ?? $this->attributes->get('custormer');

        if (! $customer instanceof Customer) {
            abort(401, 'Customer is not authenticated.');
        }

        return $customer;
    }
}
