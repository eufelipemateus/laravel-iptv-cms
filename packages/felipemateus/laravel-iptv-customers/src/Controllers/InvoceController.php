<?php

namespace FelipeMateus\IPTVCustomers\Controllers;

use Illuminate\Http\Request;
use FelipeMateus\IPTVCore\Controllers\CoreController;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomerInvoce;
use FelipeMateus\IPTVCustomers\Models\IPTVCustomer;
use FelipeMateus\IPTVCustomers\Requests\IPTVCustomerInvoceCreateInvoceRequest;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use FelipeMateus\IPTVCore\Model\IPTVConfig;

use DateTime;

class InvoceController extends CoreController
{
    //
    public function new($customer_id){
        // $data['CustomerInvoce'] = IPTVCustomerInvoce::where("iptv_customer_id",$customer_id);
        return view("IPTV::customer_invoce");
    }

    public function create($customer_id, IPTVCustomerInvoceCreateInvoceRequest $request){
        $input = $request->all();
        $data['iptv_customer_id'] = $customer_id;
        $data['duedate_at'] = $input['duedate_at'];
        //$data['payeddate_at'] = $dateTime = (new DateTime($input['payeddate_at']))->getTimestamp();

		IPTVCustomerInvoce::create($data);

        return redirect()->route('show_customer', ['id'=>$customer_id]);

    }

    public function pay($customer_id, $id){
        $data['invoce'] = IPTVCustomerInvoce::find($id);
        $data['GatewaysList'] = IPTVGateway::where('active', true)->get();
        $data['ConfigData'] = IPTVConfig::getAllStringSettings();

        $data['subtotal'] = 0;
        $data['totalDiscount'] = 0;
        $data['total'] = 0 ;
        $data['totalTax'] = 0;
        $data['final'] = 0;

        $customer = IPTVCustomer::find($customer_id);
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

        return view("IPTV::invoce", $data);
    }

    public function cancel($customer_id, $id){
        $invoce = IPTVCustomerInvoce::find($id);
        $invoce->canceled_at = now();
        $invoce->save();
        return redirect()->route('show_customer', ['id'=>$customer_id]);
    }
}
