<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Channel;
use App\Models\IPTVConfig;

class ChannelListM3UController  extends Controller{

    /**
     *  This fucntion return file M3U to list to player
     *
     * @return response
     */
	public function show($slug){
		$data["list"] = Channel::getListM3u8($slug);

		$response = response()->view("list_M3U",$data, 200);
        $response->header('Content-Type', "text/plain; charset=utf-8");

        if(IPTVConfig::get('DOWNLOAD_FILE')){
            $response->header('Cache-Control', 'public');
            $response->header('Content-Description', 'File Transfer');
            $response->header('Content-Disposition', 'attachment; filename=' . $slug . '.m3u8');
        }
        return $response;
	}
}
