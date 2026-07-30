<?php

namespace App\Http\Controllers;

use App\Actions\Customers\DeleteCustomerAction;
use App\Actions\Customers\StoreCustomerAction;
use App\Actions\Customers\UpdateCustomerAction;
use App\Http\Requests\DeleteCustomerRequest;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Services\OperationModeService;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use Illuminate\Http\RedirectResponse;

class CustomerController extends Controller
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
        // //$this->middleware('auth');
    }

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Show new customer page.
     *
     * @return view -> customer
     */
    public function new()
    {
        $data['Planslist'] = CustomerPlan::activePlanList();
        $data['Cdnslist'] = ChannelCdn::all();
        $data['show_m3u8_features'] = $this->operationModeService->isM3u8();

        return view('customer', $data);
    }

    /**
     * Show page from customer with id.
     *
     * @param  $id  - customer id
     * @return view -> IPTV::customer
     */
    public function show($id)
    {
        $data['Customer'] = Customer::findOrFail($id);
        $data['Planslist'] = CustomerPlan::activePlanList();
        $data['PlansAdditionallist'] = $data['Customer']->planAditionalList();
        $data['Cdnslist'] = ChannelCdn::all();
        $data['CustomerPlansAddionalList'] = $data['Customer']->plans_additional()->get();
        $data['CustomerInvoceList'] = $data['Customer']->customer_invoce()->get();
        $data['show_m3u8_features'] = $this->operationModeService->isM3u8();
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVGateway')) {
            $data['GatewaysList'] = IPTVGateway::where('active', 1)->get();
        } else {
            $data['GatewaysList'] = [];
        }

        return view('customer', $data);
    }

    /**
     * Save new data from new customer in database.
     *
     * @return redirect -> show_costumer
     */
    public function create(StoreCustomerRequest $request): RedirectResponse
    {
        $customer = StoreCustomerAction::run($request->validated());

        return redirect()->route('show_customer', ['id' => $customer->id])
            ->with('auth_token', $customer->getRelation('plainAuthToken'));
    }

    /**
     * Update customer in database.
     *
     * @param id from customer
     * @return redirect -> list_customers
     */
    public function update($id, UpdateCustomerRequest $request): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        UpdateCustomerAction::run(
            $customer,
            $request->validated(),
            $request->boolean('active'),
            $request->filled('regenerate'),
            $request->filled('revoke_token'),
        );

        $redirect = redirect()->route('show_customer', ['id' => $customer->id]);

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
    public function delete(DeleteCustomerRequest $request): RedirectResponse
    {
        DeleteCustomerAction::run(Customer::findOrFail($request->id()));

        return redirect()->route('list_customer');
    }

    /**
     * Return a customer List from database.
     *
     * @return view -> IPTV::customer_list
     */
    public function list()
    {
        $data['list'] = Customer::getList();

        return view('customer_list', $data);
    }
}
