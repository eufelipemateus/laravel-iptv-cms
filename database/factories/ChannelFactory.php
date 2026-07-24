<?php

namespace Database\Factories;

use App\Models\Channel;
use App\Models\ChannelGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'group_id' => ChannelGroup::factory(),
            'number' => $this->faker->unique()->numberBetween(1, 9999),
            'name' => $this->faker->unique()->words(2, true),
            'logo' => 'logos/test-channel.png',
            'radio' => false,
        ];
    }

    public function radio(): static
    {
        return $this->state(fn () => [
            'radio' => true,
        ]);
    }
}
