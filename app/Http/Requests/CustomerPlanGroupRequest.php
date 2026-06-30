<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CustomerPlanGroupRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'plan_id' => $this->route('plan_id'),
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
            'plan_id' => ['required', 'integer', 'exists:iptv_plans,id'],
            'iptv_group_id' => ['required', 'integer', 'exists:iptv_channel_groups,id'],
        ];
    }

    public function planId(): int
    {
        return $this->integer('plan_id');
    }
}
