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
        Schema::create('iptv_customer_plan_additionals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_customer_id')->constrained('iptv_customers');
            $table->foreignId('iptv_plans_id')->constrained('iptv_plans');
        });
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
