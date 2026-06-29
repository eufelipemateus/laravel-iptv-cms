<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\ChannelUrl;



class ChannelUrlController extends Controller
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
			'url_stream' => 'required|string|max:2048',
		]);
		$data = $request->only(['iptv_cdn_id', 'iptv_channel_id', 'url_stream']);
		$c = ChannelUrl::create($data);
		return redirect()->route('show_channel',  ['id' => $data['iptv_channel_id']]);
	}

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
	public function update($id,Request $request){
		$url = ChannelUrl::findOrFail($id);

        $this->validate($request, [
		    'iptv_cdn_id'=> 'required|exists:iptv_cdns,id',
            'iptv_channel_id' => 'required|exists:iptv_channels,id',
			'url_stream' => 'required|string|max:2048',
		]);

		$data = $request->only(['iptv_cdn_id', 'iptv_channel_id', 'url_stream']);
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
		$url =ChannelUrl::findOrFail($id);
        $channelId = $url->iptv_channel_id;
		$url->delete();
		return redirect()->route('show_channel',['id'=>$channelId]);
	}

}
