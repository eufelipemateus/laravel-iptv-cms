<?php

namespace  FelipeMateus\IPTVGatewayPayment\Controllers;

use Illuminate\Http\Request;
use FelipeMateus\IPTVCore\Controllers\CoreController;
use FelipeMateus\IPTVGatewayPayment\Models\IPTVTaxVat;
use FelipeMateus\IPTVGatewayPayment\Requests\TaxVatRequest;

class TaxVatController extends CoreController
{
    //
    public function list(){
        $data['list'] = IPTVTaxVat::all();
        return view("IPTV::list_tax", $data);
    }


    public function new(){
        return view("IPTV::tax");
    }

    public function create(TaxVatRequest $request){
		$data = $request->all();
		IPTVTaxVat::create($data);
        return redirect()->route('list_tax');
    }

    public function show($id){
       $data['Tax'] = IPTVTaxVat::findOrFail($id);
       return view("IPTV::tax", $data);
    }

    public function update($id, TaxVatRequest $request){
        $tax = IPTVTaxVat::findOrFail($id);

        $data = $request->all();
        $data['active'] = $request->boolean('active','bool');
		$tax->update($data);

        return redirect()->route('list_tax');
    }

   /**
     * Delete taxVat form database.
     *
     * @param id from taxvat
     * @return redirect -> list_customer
     */
    public function delete($id,Request $request){
		$group =IPTVTaxVat::findOrFail($id);
		$group->delete();
		return redirect()->route('list_tax');
	}
}
