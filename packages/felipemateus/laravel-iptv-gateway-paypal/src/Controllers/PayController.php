<?php

namespace FelipeMateus\IPTVPaypal\Controllers;

use FelipeMateus\IPTVCore\Controllers\CoreController;
use Illuminate\Http\Request;

use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use PayPalCheckoutSdk\Orders\OrdersCreateRequest;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;

use FelipeMateus\IPTVPaypal\Helpers\PaypalClient;

use FelipeMateus\IPTVCustomers\Models\IPTVCustomerInvoce;

class PayController extends CoreController
{
    private $code = 'paypal';

    function __construct(){

        $gateway = IPTVGateway::where("code",$this->code)->firstOrFail();
        $paypal_config = json_decode($gateway->config_data);

        $clientId =  $paypal_config->client_id;
        $clientSecret = $paypal_config->client_secret;
        $enviroment = $paypal_config->enviroment;

        $this->client = PaypalClient::client($enviroment, $clientId, $clientSecret);
    }

    public function checkout(Request $request){

        $data = $request->all();

        $orderRquest = new OrdersCreateRequest();
        $orderRquest->prefer('return=representation');
        $orderRquest->body = [
            "intent" => "CAPTURE",
            "purchase_units" => [
                [
                    "reference_id" => $data['invoce_id'],
                    "amount" => [
                        "value" => $data['invoce_price'],
                        "currency_code" => "BRL",
                    ],

                ]
            ],
            "application_context" => [
                 "cancel_url" => route('cancelled_paypal'),
                 "return_url" => route('approved_paypal'),
                 'landing_page' => 'BILLING',
                 'user_action' => 'PAY_NOW',
            ]
        ];

        try {
            $response =  $this->client->execute($orderRquest);
            return redirect()->away($response->result->links[1]->href);
        }catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }

    }

    public function approved(Request $req){
        $data = $req->query();
        $orderId = $data['token'];
        $request = new OrdersGetRequest($orderId);
        try {
            $response =  $this->client->execute($request);
            if( $response->result->status == 'APPROVED'){
                $invoce =   IPTVCustomerInvoce::findOrFail($response->result->purchase_units[0]->reference_id);
                $invoce->payment_at = now();
                $invoce->payment_data =  json_encode($response);
                $invoce->save();
            }
        }catch (HttpException $ex) {
            echo $ex->statusCode;
            print_r($ex->getMessage());
        }


       return view("IPTV::approved");
    }

    public function cancelled(Request $req){
        return view("IPTV::cancelled");
    }

}
