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
        Schema::create('iptv_customer_invoces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_customer_id')->constrained('iptv_customers');
            $table->date('duedate_at')->unique();
            $table->timestamp('payment_at')->nullable();
            $table->timestamp('canceled_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_customer_invoces');
    }
};
