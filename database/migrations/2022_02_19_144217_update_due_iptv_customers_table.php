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
        //
        Schema::table('iptv_customers', function (Blueprint $table) {
            $table->enum('due_day', [5,10,15,20,25])->default(15);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //

        Schema::table('iptv_customers', function (Blueprint $table) {
            $table->dropColumn('due_day');
       });
    }
};
