<?php

namespace Database\Factories;

use App\Models\IPTVConfig;
use Illuminate\Database\Eloquent\Factories\Factory;

class IPTVConfigFactory extends Factory
{
    protected $model = IPTVConfig::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'val' => $this->faker->word(),
            'type' => 'string',
        ];
    }

    public function boolean(bool $value = true): static
    {
        return $this->state(fn () => [
            'val' => $value ? '1' : '0',
            'type' => 'bool',
        ]);
    }
}
