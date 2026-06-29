<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CustomerPlan;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVTaxVat;

class CustomerPlanController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Return new page _blank.
     *
     * @return view -> customer_plan
     */
	public function new(){
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVTaxVat')) {
            $data['TaxVatList'] = IPTVTaxVat::where('active', true)->get();
        } else {
            $data['TaxVatList'] = [];
        }
		return view("customer_plan", $data);
	}

    /**
     * Create new channel in database.
     *
     * @return redirect -> list_customer_plan
     */
    public function create(Request $request){
		$data = $this->validatedPlanData($request);
		CustomerPlan::create($data);
		return redirect()->route('list_customer_plan');
	}

    /**
     * Return a page with group from database.
     *
     * @param id -> from plan
     * @return redirect -> list_customer_plan
     */
    public function show($id){
		$data["Plan"] = CustomerPlan::findOrFail($id);
        $data['GroupList'] = $data["Plan"]->groupsList();
        $data['PlanGroupList'] =$data["Plan"]->groups;
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVTaxVat')) {
            $data['TaxVatList'] = IPTVTaxVat::where('active', true)->get();
        } else {
            $data['TaxVatList'] = [];
        }
        return view("customer_plan",$data);
	}

    /**
     * Update group in database
     *
     * @param id from plan
     * @return redirect -> list_customer_plan
     */
    public function update($id,Request $request){
		$plan =CustomerPlan::findOrFail($id);
        $data = $this->validatedPlanData($request);

		$plan->update($data);
		return redirect()->route('list_customer_plan');
	}

    /**
     * Delete plan from database
     *
     * @param id from plan
     * @return redirect -> list_customer_plan
     */
    public function delete($id,Request $request){
		$plan =CustomerPlan::findOrFail($id);
		$plan->delete();
		return redirect()->route('list_customer_plan');
	}

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_customer_plan
     */
    public function list(){
		$data["list"] = CustomerPlan::get();
		return view("customer_plan_list",$data);
	}

    private function validatedPlanData(Request $request): array
    {
        if ($request->input('iptv_tax_vat_id') === 'null') {
            $request->merge(['iptv_tax_vat_id' => null]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0|max:999999.99',
            'active' => 'sometimes|boolean',
            'additional' => 'sometimes|boolean',
            'iptv_tax_vat_id' => 'nullable|exists:iptv_tax_vat,id',
        ]);

        $data['active'] = $request->boolean('active');
        $data['additional'] = $request->boolean('additional');

        return $data;
    }

}
