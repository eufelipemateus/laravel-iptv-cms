<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerPlanAdditionalRequest extends FormRequest
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
            'iptv_plan_id' => [
                'required',
                'integer',
                Rule::exists('iptv_plans', 'id')->where(fn ($query) => $query->where('additional', 1)),
            ],
        ];
    }
}
