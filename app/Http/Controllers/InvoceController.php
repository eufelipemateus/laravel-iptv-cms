<?php

namespace App\Http\Controllers;

use App\Actions\CustomerInvoces\CancelCustomerInvoceAction;
use App\Actions\CustomerInvoces\GetCustomerInvocePaymentDataAction;
use App\Actions\CustomerInvoces\StoreCustomerInvoceAction;
use App\Http\Requests\CancelCustomerInvoceRequest;
use App\Http\Requests\IPTVCustomerInvoceCreateInvoceRequest;
use App\Http\Requests\PayCustomerInvoceRequest;
use App\Models\Customer;
use App\Models\CustomerInvoce;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class InvoceController extends Controller
{
    //
    public function new(Customer $customer): View
    {
        return view('customer_invoce');
    }

    public function create(Customer $customer, IPTVCustomerInvoceCreateInvoceRequest $request): RedirectResponse
    {
        StoreCustomerInvoceAction::run($customer, $request->validated());

        return redirect()->route('show_customer', ['customer' => $customer]);

    }

    public function pay(Customer $customer, CustomerInvoce $customerInvoce, PayCustomerInvoceRequest $request): View
    {
        return view('invoce', GetCustomerInvocePaymentDataAction::run($customer, $customerInvoce));
    }

    public function cancel(Customer $customer, CustomerInvoce $customerInvoce, CancelCustomerInvoceRequest $request): RedirectResponse
    {
        CancelCustomerInvoceAction::run($customer, $customerInvoce);

        return redirect()->route('show_customer', ['customer' => $customer]);
    }
}
