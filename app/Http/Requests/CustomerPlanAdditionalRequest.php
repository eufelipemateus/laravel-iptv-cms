<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPlanAdditionalRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'customer_id' => $this->route('customer_id'),
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
            'iptv_plan_id' => [
                'required',
                'integer',
                Rule::exists('iptv_plans', 'id')->where(fn ($query) => $query->where('additional', 1)),
            ],
        ];
    }

    public function customerId(): int
    {
        return $this->integer('customer_id');
    }
}
