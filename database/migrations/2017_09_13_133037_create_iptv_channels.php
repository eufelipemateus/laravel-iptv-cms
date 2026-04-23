<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iptv_channels', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('group_id');
			$table->integer("number")->unique();
			$table->string("name",60);
			$table->text("logo",200)->nullable();
			$table->boolean('radio')->default(false);
            $table->timestamps();

			$table->foreign('group_id')->references('id')->on('iptv_channel_groups');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_channels');
    }
};
