<?php

namespace Database\Factories;

use App\Models\ChannelGroup;
use App\Models\CustomerPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelGroupFactory extends Factory
{
    protected $model = ChannelGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'iptv_plan_id' => null,
        ];
    }

    public function forPlan(?CustomerPlan $plan = null): static
    {
        return $this->state(fn () => [
            'iptv_plan_id' => $plan?->id ?? CustomerPlan::factory(),
        ]);
    }
}
