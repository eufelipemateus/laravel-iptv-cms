<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ChannelCdn;
use App\Models\IPTVConfig;
use App\Models\CustomerCdn;
use Illuminate\Http\RedirectResponse;

class ChannelCdnController extends Controller
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
		return view("channel_cdn");
	}

    /**
     * Show page from channel with id.
     *
     * @param $id - channewl id
     * @return view -> IPTV:chanel
     */
	public function show($id){
		$data["cdn"] = ChannelCdn::findOrFail($id);
		//$data["Groupslist"] = IPTVChannelGroup::get();
		return view("channel_cdn",$data);
	}

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(Request $request){
		$this->validate($request, [
			'slug' => 'required|string|alpha_dash|max:50|unique:iptv_cdns',
			'name' => 'required|string|max:90',
		]);
		$data = $request->only(['slug', 'name']);
		$c = ChannelCdn::create($data);
		// Save Image
		$c->save();
		return redirect()->route('list_channel_cdn');
	}

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
	public function update($id,Request $request){
		$cdn = ChannelCdn::findOrFail($id);

		$this->validate($request, [
			'slug' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique('iptv_cdns')->ignore($cdn->id, 'id')],
			'name' => 'required|string|max:90',
		]);

		$data = $request->only(['slug', 'name']);
		$cdn->update($data);

		return redirect()->route('list_channel_cdn');
	}

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id,Request $request){
		$cdn =ChannelCdn::findOrFail($id);
		$cdn->delete();
		return redirect()->route('list_channel_cdn');
	}

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list(){

        if (class_exists(CustomerCdn::class)) {
            $data['list'] = CustomerCdn::all();
        } else {
            $data['list'] = ChannelCdn::all();
        }

        $data['url_cdn'] = IPTVConfig::get('URL_CDN');
        $data['donwload'] =  IPTVConfig::get('DOWNLOAD_FILE');
		return view("channel_cdn_list",$data);
	}
}
