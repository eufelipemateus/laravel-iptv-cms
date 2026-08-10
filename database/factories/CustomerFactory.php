<?php

namespace Database\Factories;

use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $tokenSecret = Str::random(64);

        return [
            'name' => $this->faker->name(),
            'username' => $this->faker->unique()->userName(),
            'auth_token_id' => (string) Str::ulid(),
            'auth_token_hash' => Hash::make($tokenSecret),
            'auth_token_last_used_at' => null,
            'auth_token_expires_at' => null,
            'auth_token_revoked_at' => null,
            'iptv_plan_id' => CustomerPlan::factory()->active(),
            'iptv_cdn_id' => ChannelCdn::factory(),
            'active' => true,
            'due_day' => $this->faker->randomElement([5, 10, 15, 20, 25]),
            'industry' => $this->faker->companySuffix(),
            'address' => $this->faker->streetAddress(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'tax_no' => (string) $this->faker->unique()->numberBetween(100000, 999999),
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

    public function defeated(): static
    {
        return $this->afterCreating(function (Customer $customer): void {
            $customer->customer_invoce()->create([
                'duedate_at' => now()->subMonthNoOverflow()->toDateString(),
            ]);
        });
    }
}
