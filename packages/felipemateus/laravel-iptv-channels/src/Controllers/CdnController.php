<?php

namespace  FelipeMateus\IPTVChannels\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use FelipeMateus\IPTVChannels\Model\IPTVCdn;
use \FelipeMateus\IPTVCustomers\Models\IPTVCdn as IPTVCdnCustomer;
use FelipeMateus\IPTVCore\Model\IPTVConfig;
use FelipeMateus\IPTVCore\Controllers\CoreController;

class CdnController extends CoreController
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
     * Show new channewl page.
     *
     * @return view -> IPTV:chanel
     */
	public function new(){
		#$data["Groupslist"] = IPTVChannelGroup::get();
		return view("IPTV::cdn");
	}

    /**
     * Show page from channel with id.
     *
     * @param $id - channewl id
     * @return view -> IPTV:chanel
     */
	public function show($id){
		$data["cdn"] = IPTVCdn::findOrFail($id);
		//$data["Groupslist"] = IPTVChannelGroup::get();
		return view("IPTV::cdn",$data);
	}

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(Request $request){
		$this->validate($request, [
			'slug' => 'required|unique:iptv_cdns',
			'name' => 'required',
		]);
		$data = $request->all();
		$c = IPTVCdn::create($data);
		// Save Image
		$c->save();
		return redirect()->route('list_cdn');
	}

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
	public function update($id,Request $request){
		$cdn = IPTVCdn::findOrFail($id);

		$this->validate($request, [
			'slug' => ['required',Rule::unique('iptv_cdns')->ignore($cdn->id, 'id')],
			'name' => 'required',
		]);

		$data = $request->all();
		$cdn->update($data);

		return redirect()->route('list_cdn');
	}

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id,Request $request){
		$cdn =IPTVCdn::findOrFail($id);
		$cdn->delete();
		return redirect()->route('list_cdn');
	}

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list(){

        if(class_exists(IPTVCdnCustomer::class)){
            $data['list'] = IPTVCdnCustomer::all();
        }else {
            $data['list'] = IPTVCdn::all();
        }

        $data['url_cdn'] = IPTVConfig::get('URL_CDN');
        $data['donwload'] =  IPTVConfig::get('DOWNLOAD_FILE');
		return view("IPTV::cdn_list",$data);
	}
}
