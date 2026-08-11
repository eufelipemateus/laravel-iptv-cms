<?php

namespace Database\Factories;

use App\Models\IPTVTaxVat;
use Illuminate\Database\Eloquent\Factories\Factory;

class IPTVTaxVatFactory extends Factory
{
    protected $model = IPTVTaxVat::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'porcent' => (string) $this->faker->randomFloat(2, 0, 25),
            'active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'active' => false,
        ]);
    }
}
