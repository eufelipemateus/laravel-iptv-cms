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
        Schema::create('iptv_urls', function (Blueprint $table) {
            $table->id();
            $table->foreignId('iptv_cdn_id');
            $table->foreignId('iptv_channel_id');
            $table->timestamps();

            $table->text('url_stream');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_urls');
    }
};
