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
        Schema::table('iptv_plans', function (Blueprint $table) {
            $table->foreignId('iptv_tax_vat_id')->nullable()->constrained('iptv_tax_vat');
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
        Schema::table('iptv_plans', function (Blueprint $table) {
            $table->dropColumn('iptv_tax_vat_id');
       });
    }
};
