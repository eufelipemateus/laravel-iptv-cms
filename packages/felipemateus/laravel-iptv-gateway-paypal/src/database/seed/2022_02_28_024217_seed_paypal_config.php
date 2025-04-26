<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(!IPTVGateway::firstWhere('code', 'paypal')){
            IPTVGateway::create([
                'code' => 'paypal',
                'name'=> "Paypal",
                'class_model' => '\FelipeMateus\IPTVPaypal\Facades\Paypal',
                'config_data' => '',
                'active'=> 1
            ]);
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        IPTVGateway::where('code','paypal')->delete();
    }
};
