<?php

namespace App\Http\Controllers;

use App\Actions\CustomerInvoces\CancelCustomerInvoceAction;
use App\Actions\CustomerInvoces\StoreCustomerInvoceAction;
use App\Http\Requests\CancelCustomerInvoceRequest;
use App\Http\Requests\IPTVCustomerInvoceCreateInvoceRequest;
use App\Http\Requests\PayCustomerInvoceRequest;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use App\Models\IPTVConfig;
use App\Services\Invoces\InvoiceCalculator;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use Illuminate\Http\RedirectResponse;

class InvoceController extends Controller
{
    //
    public function new($customer_id)
    {
        // $data['CustomerInvoce'] = IPTVCustomerInvoce::where("iptv_customer_id",$customer_id);
        return view('customer_invoce');
    }

    public function create(IPTVCustomerInvoceCreateInvoceRequest $request): RedirectResponse
    {
        StoreCustomerInvoceAction::run($request->customerId(), $request->validated());

        return redirect()->route('show_customer', ['id' => $request->customerId()]);

    }

    public function pay(PayCustomerInvoceRequest $request)
    {
        $data['invoce'] = CustomerInvoce::findOrFail($request->invoceId());
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVGateway')) {
            $data['GatewaysList'] = IPTVGateway::where('active', 1)->get();
        } else {
            $data['GatewaysList'] = [];
        }
        $data['ConfigData'] = IPTVConfig::getAllStringSettings();

        $customer = Customer::findOrFail($request->customerId());
        $data = array_merge($data, app(InvoiceCalculator::class)->calculate($customer));

        return view('invoce', $data);
    }

    public function cancel(CancelCustomerInvoceRequest $request): RedirectResponse
    {
        CancelCustomerInvoceAction::run(CustomerInvoce::findOrFail($request->invoceId()));

        return redirect()->route('show_customer', ['id' => $request->customerId()]);
    }
}
