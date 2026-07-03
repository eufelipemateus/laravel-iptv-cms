<?php

namespace Tests\Unit\Models;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Tests\TestCase;

class CustomerDefeatedTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Date::setTestNow();

        parent::tearDown();
    }

    public function test_overdue_invoice_from_another_customer_does_not_mark_customer_as_defeated(): void
    {
        Date::setTestNow('2026-06-15 10:00:00');

        $customerA = Customer::factory()->active()->create();
        $customerB = Customer::factory()->active()->create();

        CustomerInvoce::factory()->overdue()->create([
            'iptv_customer_id' => $customerA->id,
            'duedate_at' => '2026-05-10',
        ]);

        $this->assertTrue($customerA->fresh()->defeated);
        $this->assertFalse($customerB->fresh()->defeated);
    }

    public function test_paid_canceled_future_and_absent_invoices_are_not_defeated(): void
    {
        Date::setTestNow('2026-06-15 10:00:00');

        $paid = Customer::factory()->active()->create();
        $canceled = Customer::factory()->active()->create();
        $future = Customer::factory()->active()->create();
        $withoutInvoices = Customer::factory()->active()->create();

        CustomerInvoce::factory()->paid()->create([
            'iptv_customer_id' => $paid->id,
            'duedate_at' => '2026-05-10',
        ]);
        CustomerInvoce::factory()->canceled()->create([
            'iptv_customer_id' => $canceled->id,
            'duedate_at' => '2026-05-10',
        ]);
        CustomerInvoce::factory()->create([
            'iptv_customer_id' => $future->id,
            'duedate_at' => '2026-07-10',
        ]);

        $this->assertFalse($paid->fresh()->defeated);
        $this->assertFalse($canceled->fresh()->defeated);
        $this->assertFalse($future->fresh()->defeated);
        $this->assertFalse($withoutInvoices->fresh()->defeated);
    }
}
