<?php

namespace FelipeMateus\IPTVPaypal\Controllers;

use FelipeMateus\IPTVCore\Controllers\CoreController;
use Illuminate\Http\Request;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;

class ConfigPayPalController extends CoreController
{
    //
    public function config(){
        $iptv_gateway = IPTVGateway::where("code","paypal")->firstOrFail();
        $data['config_paypal'] =  @json_decode($iptv_gateway->config_data);
        return view('IPTV::config_paypal', $data);
    }

    public function save_config(Request $request){
        $iptv_gateway = IPTVGateway::where("code","paypal")->firstOrFail();

        $data['client_id'] =  $request->input('client_id');
        $data['client_secret'] =  $request->input('client_secret');
        $data['enviroment'] = $request->input('enviroment');

        $iptv_gateway->config_data =  @json_encode($data);
        $iptv_gateway->save();

        return redirect()->route('cofig_paypal');
    }

}
