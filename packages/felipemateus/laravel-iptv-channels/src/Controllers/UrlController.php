<?php

namespace  FelipeMateus\IPTVChannels\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use FelipeMateus\IPTVChannels\Model\IPTVCdn;
use FelipeMateus\IPTVChannels\Model\IPTVUrl;
use FelipeMateus\IPTVCore\Controllers\CoreController;


class UrlController extends CoreController
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
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create( Request $request){
		$this->validate($request, [
		    'iptv_cdn_id'=> 'required|exists:iptv_cdns,id',
            'iptv_channel_id' => 'required|exists:iptv_channels,id',
			'url_stream' => 'required',
		]);
		$data = $request->all();
		$c = IPTVUrl::create($data);
		return redirect()->route('show_channel',  ['id' => $data['iptv_channel_id']]);
	}

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
	public function update($id,Request $request){
		$url = IPTVUrl::findOrFail($id);

        $this->validate($request, [
		    'iptv_cdn_id'=> 'required|exists:iptv_cdns,id',
            'iptv_channel_id' => 'required|exists:iptv_channels,id',
			'url_stream' => 'required',
		]);

		$data = $request->all();
		$url->update($data);

		return redirect()->route('show_channel',['id'=>$data['iptv_channel_id']]);
	}

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id,Request $request){
		$url =IPTVUrl::findOrFail($id);
		$url->delete();
		return redirect()->route('show_channel',['id'=>$url->iptv_channel_id]);
	}

}
