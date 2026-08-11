<?php

namespace Tests\Unit\Services;

use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Services\Invoces\InvoiceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_calculates_main_plan_additional_plans_tax_and_totals(): void
    {
        $main = CustomerPlan::factory()->active()->withTax(10)->create([
            'name' => 'Main',
            'price' => 100,
        ]);
        $additional = CustomerPlan::factory()->active()->additional()->withTax(5)->create([
            'name' => 'Extra',
            'price' => 50,
        ]);
        $customer = Customer::factory()->active()->create(['iptv_plan_id' => $main->id]);
        $customer->plans_additional()->syncWithoutDetaching([$additional->id]);

        $result = app(InvoiceCalculator::class)->calculate($customer->fresh());

        $this->assertCount(2, $result['services']);
        $this->assertSame(150.0, $result['subtotal']);
        $this->assertSame(0.0, $result['totalDiscount']);
        $this->assertSame(150.0, $result['total']);
        $this->assertSame(12.5, $result['totalTax']);
        $this->assertSame(162.5, $result['final']);
    }

    public function test_plan_without_tax_uses_zero_tax(): void
    {
        $plan = CustomerPlan::factory()->active()->create([
            'price' => 19.99,
            'iptv_tax_vat_id' => null,
        ]);
        $customer = Customer::factory()->active()->create(['iptv_plan_id' => $plan->id]);

        $result = app(InvoiceCalculator::class)->calculate($customer);

        $this->assertSame(19.99, $result['subtotal']);
        $this->assertSame(0.0, $result['totalTax']);
        $this->assertSame(19.99, $result['final']);
    }
}
