<?php

namespace Tests\Integration\Database;

use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\CustomerPlan;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
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
            'auth_token_id',
            'auth_token_hash',
            'auth_token_last_used_at',
            'auth_token_expires_at',
            'auth_token_revoked_at',
        ]));
    }

    public function test_additional_plan_unique_index_is_mysql_safe_and_recovers_a_partial_migration(): void
    {
        $tableName = 'iptv_customer_plan_additionals';
        $columns = ['iptv_customer_id', 'iptv_plans_id'];
        $index = collect(Schema::getIndexes($tableName))
            ->first(fn (array $index) => $index['columns'] === $columns && $index['unique']);

        $this->assertNotNull($index);
        $this->assertLessThanOrEqual(64, strlen($index['name']));

        $customer = Customer::factory()->create();
        $plan = CustomerPlan::factory()->create();

        Schema::drop($tableName);
        Schema::create($tableName, function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_customer_id')->constrained('iptv_customers');
            $table->foreignId('iptv_plans_id')->constrained('iptv_plans');
        });

        $row = ['iptv_customer_id' => $customer->id, 'iptv_plans_id' => $plan->id];
        DB::table($tableName)->insert($row);

        $migration = require database_path('migrations/2022_01_08_025029_create_iptv_customer_plan_additionals_table.php');
        $migration->up();

        $this->assertDatabaseHas($tableName, $row);
        $this->assertTrue(Schema::hasIndex($tableName, $columns, 'unique'));

        $this->expectException(QueryException::class);
        DB::table($tableName)->insert($row);
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
