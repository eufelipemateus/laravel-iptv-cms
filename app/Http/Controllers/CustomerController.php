<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ChannelCdn;
use App\Models\CustomerPlan;
use App\Models\Customer;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVGateway;
use Illuminate\Support\Str;

class CustomerController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        ////$this->middleware('auth');
    }

    /**
     * Show new customer page.
     *
     * @return view -> customer
     */
	public function new(){
		$data["Planslist"] = CustomerPlan::activePlanList();
        $data['Cdnslist'] = ChannelCdn::all();
		return view("customer",$data);
	}

    /**
     * Show page from customer with id.
     *
     * @param $id - customer id
     * @return view -> IPTV::customer
     */
	public function show($id){
		$data["Customer"]  = Customer::findOrFail($id);
        $data["Planslist"] = CustomerPlan::activePlanList();
        $data["PlansAdditionallist"] = $data["Customer"]->planAditionalList();
        $data['Cdnslist'] = ChannelCdn::all();
        $data['CustomerPlansAddionalList'] = $data["Customer"]->plans_additional()->get();
        $data['CustomerInvoceList'] = $data["Customer"]->customer_invoce()->get();
        if (class_exists('FelipeMateus\\IPTVGatewayPayment\\Models\\IPTVGateway')) {
            $data['GatewaysList'] = IPTVGateway::where('active',1)->get();
        } else {
            $data['GatewaysList'] = [];
        }

        return view("customer",$data);
	}

    /**
     * Save new data from new customer in database.
     *
     * @return redirect -> show_costumer
     */
    public function create(Request $request){
		$this->validate($request, [
			'name' => 'string|required|max:255',
			'username' => 'required|string|max:255|unique:iptv_customers,username',
			'iptv_plan_id' => 'required|exists:iptv_plans,id',
            'iptv_cdn_id' => 'nullable|exists:iptv_cdns,id',
            'due_day' => 'required|in:5,10,15,20,25',
            'industry'=>'string|nullable|max:255',
            'address'=>'string|nullable|max:255',
            'phone'=>'string|nullable|max:255',
            'email'=>'email|nullable|max:255',
            'tax_no'=>'string|nullable|max:255',
		]);
		$data = $request->only([
            'name',
            'username',
            'iptv_plan_id',
            'iptv_cdn_id',
            'due_day',
            'industry',
            'address',
            'phone',
            'email',
            'tax_no',
        ]);
        $data['active'] = false;
        $data['hash_acess'] = Str::random(40);
        $customer  = 	Customer::create($data);
        return redirect()->route('show_customer',['id'=>$customer->id]);
	}

    /**
     * Update customer in database.
     *
     * @param id from customer
     * @return redirect -> list_customers
     */
	public function update($id,Request $request){
		$customer = Customer::findOrFail($id);

        $regenerate = $request->input("regenerate");

        if(!empty($regenerate)){
            $customer->update(['hash_acess' => Str::random(40)]);
            return redirect()->route('show_customer',['id'=>$customer->id]);
        }

		$this->validate($request, [
			'name' => 'string|required|max:255',
			'username' => ['required', 'string', 'max:255', Rule::unique('iptv_customers', 'username')->ignore($customer->id, 'id')],
			'iptv_plan_id' => 'required|exists:iptv_plans,id',
            'iptv_cdn_id' => 'nullable|exists:iptv_cdns,id',
            'due_day' => 'required|in:5,10,15,20,25',
            'industry'=>'string|nullable|max:255',
            'address'=>'string|nullable|max:255',
            'phone'=>'string|nullable|max:255',
            'email'=>'email|nullable|max:255',
            'tax_no'=>'string|nullable|max:255',
            'active'=>'boolean',
		]);

		$data = $request->only([
            'name',
            'username',
            'iptv_plan_id',
            'iptv_cdn_id',
            'due_day',
            'industry',
            'address',
            'phone',
            'email',
            'tax_no',
        ]);
        $data['active'] = $request->boolean('active');
		$customer->update($data);

        return redirect()->route('show_customer',['id'=>$customer->id]);
	}

    /**
     * Delete customer form database.
     *
     * @param id from customer
     * @return redirect -> list_customer
     */
    public function delete($id,Request $request){
		$customer = Customer::findOrFail($id);
		$customer->delete();
		return redirect()->route('list_customer');
	}

    /**
     * Return a customer List from database.
     *
     * @return view -> IPTV::customer_list
     */
    public function list(){
		$data['list'] = Customer::getList();
		return view("customer_list",$data);
	}
}
