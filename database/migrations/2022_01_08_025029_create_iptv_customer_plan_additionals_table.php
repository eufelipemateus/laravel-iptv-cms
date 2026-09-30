<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = 'iptv_customer_plan_additionals';
        $columns = ['iptv_customer_id', 'iptv_plans_id'];
        $indexName = 'iptv_customer_plan_additional_unique';

        if (! Schema::hasTable($tableName)) {
            Schema::create($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->id();
                $table->foreignId('iptv_customer_id')->constrained('iptv_customers');
                $table->foreignId('iptv_plans_id')->constrained('iptv_plans');
                $table->unique($columns, $indexName);
            });

            return;
        }

        // MySQL may leave the table behind when adding the original index fails.
        if (! Schema::hasIndex($tableName, $columns, 'unique')) {
            Schema::table($tableName, function (Blueprint $table) use ($columns, $indexName) {
                $table->unique($columns, $indexName);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_customer_plan_additionals');
    }
};
