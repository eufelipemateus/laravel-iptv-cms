<?php

namespace Tests\Feature\Commands;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class GenerateInvocesCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Date::setTestNow();

        parent::tearDown();
    }

    public function test_monthly_invoice_command_generates_only_active_customers_with_correct_customer_key(): void
    {
        Date::setTestNow('2026-06-10 09:00:00');
        $active = Customer::factory()->active()->create(['due_day' => 15]);
        $inactive = Customer::factory()->inactive()->create(['due_day' => 15]);

        $this->artisan('invoce:month')
            ->expectsOutput('Generating invoices for the month 06/2026.')
            ->expectsOutput('All Invoces generate successfully.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('iptv_customer_invoces', [
            'iptv_customer_id' => $active->id,
            'duedate_at' => '2026-06-15',
        ]);
        $this->assertDatabaseMissing('iptv_customer_invoces', [
            'iptv_customer_id' => $inactive->id,
        ]);
    }

    public function test_monthly_invoice_command_is_idempotent_for_same_customer_and_period(): void
    {
        Date::setTestNow('2026-06-10 09:00:00');
        $customer = Customer::factory()->active()->create(['due_day' => 20]);

        $this->artisan('invoce:month')->assertExitCode(0);
        $this->artisan('invoce:month')->assertExitCode(0);

        $this->assertSame(1, $customer->customer_invoce()->where('duedate_at', '2026-06-20')->count());
    }
}
