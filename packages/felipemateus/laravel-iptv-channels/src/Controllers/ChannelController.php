<?php

namespace  FelipeMateus\IPTVChannels\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use FelipeMateus\IPTVChannels\Model\IPTVChannelGroup;
use FelipeMateus\IPTVChannels\Model\IPTVChannel;
use FelipeMateus\IPTVChannels\Model\IPTVUrl;
use FelipeMateus\IPTVChannels\Model\IPTVCdn;
use FelipeMateus\IPTVCore\Model\IPTVConfig;
use FelipeMateus\IPTVCore\Controllers\CoreController;

class ChannelController extends CoreController
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
		$data["Groupslist"] = IPTVChannelGroup::get();
        $data['radio_stream'] = IPTVConfig::get("RADIO_STREAM");
		return view("IPTV::channel",$data);
	}

    /**
     * Show page from channel with id.
     *
     * @param $id - channewl id
     * @return view -> IPTV:chanel
     */
	public function show($id){
		$data["Channel"] = IPTVChannel::findOrFail($id);
        $data["Groupslist"] = IPTVChannelGroup::get();
        $data['Cdnslist'] = IPTVCdn::all();
        $data["urls"] = IPTVUrl::where("iptv_channel_id", $id )->get();
        $data['radio_stream'] = IPTVConfig::get("RADIO_STREAM");
		return view("IPTV::channel",$data);
	}

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(Request $request){
		$this->validate($request, [
			'number' => 'numeric|required|unique:iptv_channels',
			'image' => 'required|image|mimes:jpeg,png,jpg|max:2048',
			'group_id' => 'required|exists:iptv_channel_groups,id',
		]);
		$data = $request->all();
		$c = IPTVChannel::create($data);
		// Save Image
		$c->logo = $request->file('image') ;
		$c->save();
		return redirect()->route('list_channel');
	}

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
	public function update($id,Request $request){
		$channel =IPTVChannel::findOrFail($id);

		$this->validate($request, [
			'number' => ['required','numeric',Rule::unique('iptv_channels')->ignore($channel->id, 'id')],
			'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
			'group_id' => 'required|exists:iptv_channel_groups,id',
		]);

		$data = $request->all();
		$channel->update($data);
		$image = $request->file('image');

		if(isset($image)){
			$channel->logo=$image;
		}
		if(!isset($data['radio'])){
			$channel->radio=false;
		}else{
			$channel->radio=true;
		}
		$channel->save();
		return redirect()->route('list_channel');
	}

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id,Request $request){
		$group =IPTVChannel::findOrFail($id);
		$group->delete();
		return redirect()->route('list_channel');
	}

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list(){
		$data['list'] = IPTVChannel::getList();
		return view("IPTV::channel_list",$data);
	}
}
