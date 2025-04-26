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
        Schema::create('iptv_customers', function (Blueprint $table) {
            $table->id();
            $table->string("name"); //Full user name
            $table->string('username')->unique(); //system user name
            $table->string("hash_acess");

            $table->foreignId('iptv_plan_id')->constrained();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_customers');
    }
};
