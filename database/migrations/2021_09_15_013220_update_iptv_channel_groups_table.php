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
        Schema::table('iptv_channel_groups', function (Blueprint $table) {

            $table->foreignId('iptv_plan_id')
                ->nullable()
                ->constrained('iptv_plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('iptv_channel_groups', function (Blueprint $table) {
            $table->dropForeign(['iptv_plan_id']);
            $table->dropColumn('iptv_plan_id');
        });
    }
};
