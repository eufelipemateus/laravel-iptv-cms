<?php

namespace App\Http\Controllers;

use App\Actions\Customers\DeleteCustomerAction;
use App\Actions\Customers\GetCustomerFormDataAction;
use App\Actions\Customers\ListCustomersAction;
use App\Actions\Customers\StoreCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // //$this->middleware('auth');
    }

    /**
     * Show new customer page.
     *
     * @return View -> customer
     */
    public function new(): View
    {
        return view('customer', GetCustomerFormDataAction::run());
    }

    /**
     * Show page from customer with id.
     *
     * @param  $id  - customer id
     * @return View -> IPTV::customer
     */
    public function show(Customer $customer): View
    {
        return view('customer', GetCustomerFormDataAction::run($customer));
    }

    /**
     * Save new data from new customer in database.
     *
     * @return redirect -> show_costumer
     */
    public function create(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = StoreCustomerAction::run($request->validated());

        return redirect()->route('show_customer', ['customer' => $customer])
            ->with('auth_token', $customer->getRelation('plainAuthToken'));
    }

    /**
     * Update customer in database.
     *
     * @param id from customer
     * @return redirect -> list_customers
     */
    public function update(Customer $customer, UpdateCustomerRequest $request): RedirectResponse
    {
        UpdateCustomerAction::run(
            $customer,
            $request->validated(),
            $request->boolean('active'),
            $request->filled('regenerate'),
            $request->filled('revoke_token'),
        );

        $redirect = redirect()->route('show_customer', ['customer' => $customer]);

        if ($request->filled('regenerate')) {
            return $redirect->with('auth_token', $customer->getRelation('plainAuthToken'));
        }

        if ($request->filled('revoke_token')) {
            return $redirect->with('auth_token_revoked', true);
        }

        return $redirect;
    }

    /**
     * Delete customer form database.
     *
     * @param id from customer
     * @return redirect -> list_customer
     */
    public function delete(Customer $customer): RedirectResponse
    {
        DeleteCustomerAction::run($customer);

        return redirect()->route('list_customer');
    }

    /**
     * Return a customer List from database.
     *
     * @return View -> IPTV::customer_list
     */
    public function list(): View
    {
        $data['list'] = ListCustomersAction::run();

        return view('customer_list', $data);
    }
}
