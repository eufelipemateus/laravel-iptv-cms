<?php

namespace App\Http\Controllers;

use App\Actions\CustomerPlans\DeleteCustomerPlanAction;
use App\Actions\CustomerPlans\GetCustomerPlanFormDataAction;
use App\Actions\CustomerPlans\ListCustomerPlansAction;
use App\Actions\CustomerPlans\StoreCustomerPlanAction;
use App\Actions\CustomerPlans\UpdateCustomerPlanAction;
use App\Http\Requests\CustomerPlanRequest;
use App\Models\CustomerPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
     * @return View -> customer_plan
     */
    public function new(): View
    {
        return view('customer_plan', GetCustomerPlanFormDataAction::run());
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
    public function show(CustomerPlan $customerPlan): View
    {
        return view('customer_plan', GetCustomerPlanFormDataAction::run($customerPlan));
    }

    /**
     * Update group in database
     *
     * @param id from plan
     * @return redirect -> list_customer_plan
     */
    public function update(CustomerPlan $customerPlan, CustomerPlanRequest $request): RedirectResponse
    {
        UpdateCustomerPlanAction::run(
            $customerPlan,
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
    public function delete(CustomerPlan $customerPlan): RedirectResponse
    {
        DeleteCustomerPlanAction::run($customerPlan);

        return redirect()->route('list_customer_plan');
    }

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_customer_plan
     */
    public function list(): View
    {
        $data['list'] = ListCustomerPlansAction::run();

        return view('customer_plan_list', $data);
    }
}
