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
		$data = $request->all();
        $data = $request->except(['iptv_tax_vat_id']);
        $tax_vat = $request->only('iptv_tax_vat_id')['iptv_tax_vat_id'];
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
        $data = $request->except(['iptv_tax_vat_id']);
        $tax_vat = $request->only('iptv_tax_vat_id')['iptv_tax_vat_id'];

		$plan->update($data);

        if(!isset($data['active'])){
			$plan->active=false;
		}else{
			$plan->active=true;
		}

        if(!isset($data['additional'])){
			$plan->additional=false;
		}else{
			$plan->additional=true;
		}

        if(isset($tax_vat)  ){
			$plan->iptv_tax_vat_id= ($tax_vat == 'null')?
                null
            :
                $tax_vat
            ;
		}

        $plan->save();
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

}
