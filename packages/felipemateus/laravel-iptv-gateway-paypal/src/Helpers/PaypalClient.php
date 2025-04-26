<?php
namespace FelipeMateus\IPTVPaypal\Helpers;

use PayPalCheckoutSdk\Core\PayPalHttpClient;

class PaypalClient {

    /**
     * Returns PayPal HTTP client instance with environment which has access
     * credentials context. This can be used invoke PayPal API's provided the
     * credentials have the access to do so.
     */
    public static function client($enviroment, $clientId, $clientSecret)
    {

        try{
            if( $enviroment  == 'production'){
                $environment = new \PayPalCheckoutSdk\Core\ProductionEnvironment($clientId, $clientSecret);
                $client = new PayPalHttpClient($environment);
            }else {
                $environment = new \PayPalCheckoutSdk\Core\SandboxEnvironment($clientId, $clientSecret);
                $client = new PayPalHttpClient($environment);
            }

            return $client;
        }catch(Exception $exception){
            print_r($exception);
            return ;
        }
    }

}
