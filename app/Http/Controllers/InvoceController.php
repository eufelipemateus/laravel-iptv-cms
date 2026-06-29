<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerInvoce;
use App\Models\Customer;
use App\Http\Requests\IPTVCustomerInvoceCreateInvoceRequest;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use App\Models\IPTVConfig;

use DateTime;

class InvoceController extends Controller
{
    //
    public function new($customer_id){
        // $data['CustomerInvoce'] = IPTVCustomerInvoce::where("iptv_customer_id",$customer_id);
        return view("IPTV::customer_invoce");
    }

    public function create($customer_id, IPTVCustomerInvoceCreateInvoceRequest $request){
        $input = $request->validated();
        $data['iptv_customer_id'] = $customer_id;
        $data['duedate_at'] = $input['duedate_at'];
        //$data['payeddate_at'] = $dateTime = (new DateTime($input['payeddate_at']))->getTimestamp();

		CustomerInvoce::create($data);

        return redirect()->route('show_customer', ['id'=>$customer_id]);

    }

    public function pay($customer_id, $id){
        $data['invoce'] = CustomerInvoce::find($id);
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVGateway')) {
            $data['GatewaysList'] = IPTVGateway::where('active',1)->get();
        } else {
            $data['GatewaysList'] = [];
        }
        $data['ConfigData'] = IPTVConfig::getAllStringSettings();

        $data['subtotal'] = 0;
        $data['totalDiscount'] = 0;
        $data['total'] = 0 ;
        $data['totalTax'] = 0;
        $data['final'] = 0;

        $customer = Customer::find($customer_id);
        $index = 0;
        $services[$index ]['service'] = $customer->plan->name;
        $services[$index ]['service_type'] = 'Principal';
        $services[$index ]['price'] = $customer->plan->price;
        $services[$index ]['discont'] = 0;
        $services[$index ]['tax'] =  ($services[$index ]['price'] - $services[$index ]['discont']) *  ( ((isset($customer->plan->tax_vat->porcent))? $customer->plan->tax_vat->porcent : 0)  /  100)      ;
        $services[$index ]['tax_porcent'] = (isset($customer->plan->tax_vat->porcent))? $customer->plan->tax_vat->porcent  :  0;
        $services[$index ]['total'] =  $services[$index ]['price'] +   $services[$index ]['tax'];
        $services[$index ]['subtotal'] = $services[$index ]['price'] - $services[$index ]['discont'];
        $index++;

        foreach($customer->plans_additional as $plan){
            $services[$index ]['service'] = $plan->name;
            $services[$index ]['service_type'] = 'Additional';
            $services[$index ]['price'] = $plan->price;
            $services[$index ]['discont'] = 0;
            $services[$index ]['tax'] =  ($services[$index ]['price'] - $services[$index ]['discont']) *    ( ((isset($plan->tax_vat->porcent))? $plan->tax_vat->porcent : 0)      /  100)      ;
            $services[$index ]['tax_porcent'] = (isset($plan->tax_vat->porcent) &&  $plan->tax_vat->porcent != null)? $plan->tax_vat->porcent  :  0;
            $services[$index ]['total'] =  $services[$index ]['price'] +   $services[$index ]['tax'];
            $services[$index ]['subtotal'] = $services[$index ]['price'] - $services[$index ]['discont'];
            $index ++;
        }

        $data['services'] = $services;
        // Total
        foreach($services as $service){
            $data['subtotal'] +=  $service['price'];
        }

        // Total Discount
        foreach($services as $service){
            $data['totalDiscount'] += $service['discont'];
        }

        // Total = Total - Discount
        foreach($services as $service){
            $data['total'] += $service['price'] - $service['discont'];
        }

        // Total tax
        foreach($services as $service){
            $data['totalTax'] += $service['tax'];
        }

        // Final
        foreach($services as $service){
            $data['final'] = $data['totalTax'] +  $data['total'];
        }

        return view("invoce", $data);
    }

    public function cancel($customer_id, $id){
        $invoce = CustomerInvoce::find($id);
        $invoce->canceled_at = now();
        $invoce->save();
        return redirect()->route('show_customer', ['id'=>$customer_id]);
    }
}
