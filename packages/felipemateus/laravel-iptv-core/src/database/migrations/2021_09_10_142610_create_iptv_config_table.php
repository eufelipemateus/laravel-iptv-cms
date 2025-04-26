<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use FelipeMateus\IPTVCore\Model\IPTVConfig;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('iptv_configs', function (Blueprint $table) {
            $table->increments('id');
            $table->string("name")->unique();
            $table->text('val');
            $table->char('type', 20)->default('string');
        });


        IPTVConfig::set('RADIO_STREAM',true,'bool');
        IPTVConfig::set('DOWNLOAD_FILE',false, 'bool');
        IPTVConfig::set('URL_CDN',false, 'bool');

        IPTVConfig::set('BSINESS_NAME','Acme Corporation', 'string');
        IPTVConfig::set('BSINESS_INDUSTRY','Software Development', 'string');
        IPTVConfig::set('BSINESS_ADDRESS','Field 3, Moon', 'string');
        IPTVConfig::set('BSINESS_PHONE','123.4456.4567', 'string');
        IPTVConfig::set('BSINESS_EMAIL','mainl@example.com', 'string');
        IPTVConfig::set('BSINESS_TAX_NO',"123478956", 'string');


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('iptv_configs');
    }
};
