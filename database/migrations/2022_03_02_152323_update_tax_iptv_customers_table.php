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
        Schema::table('iptv_plans', function (Blueprint $table) {
            $table->unsignedInteger('iptv_tax_vat_id')->nullable();
            $table->foreign('iptv_tax_vat_id')->references('id')->on('iptv_tax_vat');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iptv_plans', function (Blueprint $table) {
            $table->dropForeign(['iptv_tax_vat_id']);
            $table->dropColumn('iptv_tax_vat_id');
        });
    }
};
