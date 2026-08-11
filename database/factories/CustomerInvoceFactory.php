<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerInvoceFactory extends Factory
{
    protected $model = CustomerInvoce::class;

    public function definition(): array
    {
        return [
            'iptv_customer_id' => Customer::factory(),
            'duedate_at' => $this->faker->dateTimeBetween('-6 months', '+2 months')->format('Y-m-d'),
            'payment_at' => null,
            'canceled_at' => null,
            'payment_data' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn () => [
            'payment_at' => now(),
        ]);
    }

    public function canceled(): static
    {
        return $this->state(fn () => [
            'canceled_at' => now(),
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn () => [
            'duedate_at' => now()->subMonthNoOverflow()->toDateString(),
            'payment_at' => null,
            'canceled_at' => null,
        ]);
    }
}
