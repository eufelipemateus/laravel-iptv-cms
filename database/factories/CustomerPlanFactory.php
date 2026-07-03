<?php

namespace Database\Factories;

use App\Models\CustomerPlan;
use App\Models\IPTVTaxVat;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerPlanFactory extends Factory
{
    protected $model = CustomerPlan::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'price' => $this->faker->randomFloat(2, 10, 300),
            'active' => true,
            'additional' => false,
            'iptv_tax_vat_id' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn () => [
            'active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }

    public function additional(): static
    {
        return $this->state(fn () => [
            'additional' => true,
        ]);
    }

    public function withTax(float $percent = 10.0): static
    {
        return $this->state(fn () => [
            'iptv_tax_vat_id' => IPTVTaxVat::factory()->state([
                'porcent' => (string) $percent,
            ]),
        ]);
    }
}
