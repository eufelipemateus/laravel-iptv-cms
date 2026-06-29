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
        Schema::table('iptv_customers', function (Blueprint $table) {

            $table->foreignId('iptv_cdn_id')
                ->nullable()
                ->constrained('iptv_cdns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iptv_customers', function (Blueprint $table) {
            $table->dropForeign(['iptv_cdn_id']);
            $table->dropColumn('iptv_cdn_id');
        });
    }
};
