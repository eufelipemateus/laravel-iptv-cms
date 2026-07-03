<?php

namespace Tests\Integration\Database;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaAndConstraintTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_and_columns_exist_after_migrations(): void
    {
        foreach ([
            'users',
            'iptv_channel_groups',
            'iptv_channels',
            'iptv_cdns',
            'iptv_urls',
            'iptv_plans',
            'iptv_customers',
            'iptv_customer_plan_additionals',
            'iptv_customer_invoces',
            'iptv_configs',
            'iptv_tax_vat',
        ] as $table) {
            $this->assertTrue(Schema::hasTable($table), "Missing table {$table}");
        }

        $this->assertTrue(Schema::hasColumns('iptv_customer_invoces', [
            'id',
            'iptv_customer_id',
            'duedate_at',
            'payment_at',
            'canceled_at',
            'payment_data',
        ]));
        $this->assertTrue(Schema::hasColumns('iptv_customers', [
            'iptv_plan_id',
            'iptv_cdn_id',
            'active',
            'due_day',
            'email',
            'tax_no',
        ]));
    }

    public function test_different_customers_can_have_invoices_with_same_due_date(): void
    {
        $customerA = Customer::factory()->active()->create();
        $customerB = Customer::factory()->active()->create();

        CustomerInvoce::factory()->create([
            'iptv_customer_id' => $customerA->id,
            'duedate_at' => '2026-06-15',
        ]);
        CustomerInvoce::factory()->create([
            'iptv_customer_id' => $customerB->id,
            'duedate_at' => '2026-06-15',
        ]);

        $this->assertDatabaseCount('iptv_customer_invoces', 2);
    }

    public function test_same_customer_cannot_have_duplicate_invoice_due_date(): void
    {
        $this->expectException(QueryException::class);

        $customer = Customer::factory()->active()->create();

        CustomerInvoce::factory()->create([
            'iptv_customer_id' => $customer->id,
            'duedate_at' => '2026-06-15',
        ]);
        CustomerInvoce::factory()->create([
            'iptv_customer_id' => $customer->id,
            'duedate_at' => '2026-06-15',
        ]);
    }
}
