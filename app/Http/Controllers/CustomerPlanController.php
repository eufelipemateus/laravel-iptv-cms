<?php

namespace App\Http\Controllers;

use App\Actions\CustomerPlans\DeleteCustomerPlanAction;
use App\Actions\CustomerPlans\StoreCustomerPlanAction;
use App\Actions\CustomerPlans\UpdateCustomerPlanAction;
use App\Http\Requests\CustomerPlanRequest;
use App\Http\Requests\DeleteCustomerPlanRequest;
use App\Models\CustomerPlan;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVTaxVat;
use Illuminate\Http\RedirectResponse;

class CustomerPlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Return new page _blank.
     *
     * @return view -> customer_plan
     */
    public function new()
    {
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVTaxVat')) {
            $data['TaxVatList'] = IPTVTaxVat::where('active', true)->get();
        } else {
            $data['TaxVatList'] = [];
        }

        return view('customer_plan', $data);
    }

    /**
     * Create new channel in database.
     *
     * @return redirect -> list_customer_plan
     */
    public function create(CustomerPlanRequest $request): RedirectResponse
    {
        StoreCustomerPlanAction::run(
            $request->validated(),
            $request->boolean('active'),
            $request->boolean('additional'),
        );

        return redirect()->route('list_customer_plan');
    }

    /**
     * Return a page with group from database.
     *
     * @param id -> from plan
     * @return redirect -> list_customer_plan
     */
    public function show($id)
    {
        $data['Plan'] = CustomerPlan::findOrFail($id);
        $data['GroupList'] = $data['Plan']->groupsList();
        $data['PlanGroupList'] = $data['Plan']->groups;
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVTaxVat')) {
            $data['TaxVatList'] = IPTVTaxVat::where('active', true)->get();
        } else {
            $data['TaxVatList'] = [];
        }

        return view('customer_plan', $data);
    }

    /**
     * Update group in database
     *
     * @param id from plan
     * @return redirect -> list_customer_plan
     */
    public function update($id, CustomerPlanRequest $request): RedirectResponse
    {
        $plan = CustomerPlan::findOrFail($id);
        UpdateCustomerPlanAction::run(
            $plan,
            $request->validated(),
            $request->boolean('active'),
            $request->boolean('additional'),
        );

        return redirect()->route('list_customer_plan');
    }

    /**
     * Delete plan from database
     *
     * @param id from plan
     * @return redirect -> list_customer_plan
     */
    public function delete(DeleteCustomerPlanRequest $request): RedirectResponse
    {
        DeleteCustomerPlanAction::run(CustomerPlan::findOrFail($request->id()));

        return redirect()->route('list_customer_plan');
    }

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_customer_plan
     */
    public function list()
    {
        $data['list'] = CustomerPlan::get();

        return view('customer_plan_list', $data);
    }
}
