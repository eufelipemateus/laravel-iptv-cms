<?php

namespace  FelipeMateus\IPTVChannels\Controllers;

use Illuminate\Http\Request;

use FelipeMateus\IPTVChannels\Model\IPTVChannel;
use FelipeMateus\IPTVCore\Model\IPTVConfig;
use FelipeMateus\IPTVCore\Controllers\CoreController;

class ChannelListM3UController  extends CoreController{

    /**
     *  This fucntion return file M3U to list to player
     *
     * @return response
     */
	public function show($slug){
		$data["list"] = IPTVChannel::getListM3u8($slug);

		$response = response()->view("IPTV::list_M3U",$data, 200);
        $response->header('Content-Type', "text/plain; charset=utf-8");

        if(IPTVConfig::get('DOWNLOAD_FILE')){
            $response->header('Cache-Control', 'public');
            $response->header('Content-Description', 'File Transfer');
            $response->header('Content-Disposition', 'attachment; filename=' . $slug . '.m3u8');
        }
        return $response;
	}
}
