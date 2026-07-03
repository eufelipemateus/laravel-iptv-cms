<?php

namespace Database\Factories;

use App\Models\ChannelCdn;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ChannelCdnFactory extends Factory
{
    protected $model = ChannelCdn::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->company();

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.$this->faker->unique()->numberBetween(100, 999),
        ];
    }

    public function enabled(): static
    {
        return $this;
    }
}
