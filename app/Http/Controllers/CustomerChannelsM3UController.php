<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerChannelsM3URequest;
use App\Models\Channel;
use App\Models\IPTVConfig;

class CustomerChannelsM3UController extends Controller
{
    /**
     *  This fucntion return file M3U to list to player
     *
     * @return response
     */
    public function show(CustomerChannelsM3URequest $request)
    {
        $slug = $request->slug();
        $customer = $request->customer();

        $data['list'] = Channel::getCustomerChannelListM3u8($slug, $customer->id);

        $response = response()->view('list_M3U', $data, 200);
        $response->header('Content-Type', 'text/plain; charset=utf-8');

        if (IPTVConfig::get('DOWNLOAD_FILE')) {
            $response->header('Cache-Control', 'public');
            $response->header('Content-Description', 'File Transfer');
            $response->header('Content-Disposition', 'attachment; filename='.$slug.'.m3u8');
        }

        return $response;
    }
}
