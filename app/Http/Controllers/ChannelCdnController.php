<?php

namespace App\Http\Controllers;

use App\Actions\ChannelCdns\DeleteChannelCdnAction;
use App\Actions\ChannelCdns\StoreChannelCdnAction;
use App\Actions\ChannelCdns\UpdateChannelCdnAction;
use App\Http\Requests\StoreChannelCdnRequest;
use App\Http\Requests\UpdateChannelCdnRequest;
use App\Models\ChannelCdn;
use App\Models\IPTVConfig;
use FelipeMateus\IPTVCustomers\Models\IPTVCdn as IPTVCdnCustomer;
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
        // //$this->middleware('auth');
    }

    /**
     * Show new channewl page.
     *
     * @return view -> IPTV:chanel
     */
    public function new()
    {
        // $data["Groupslist"] = IPTVChannelGroup::get();
        return view('channel_cdn');
    }

    /**
     * Show page from channel with id.
     *
     * @param  $id  - channewl id
     * @return view -> IPTV:chanel
     */
    public function show($id)
    {
        $data['cdn'] = ChannelCdn::findOrFail($id);

        // $data["Groupslist"] = IPTVChannelGroup::get();
        return view('channel_cdn', $data);
    }

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(StoreChannelCdnRequest $request): RedirectResponse
    {
        StoreChannelCdnAction::run($request->validated());

        return redirect()->route('list_channel_cdn');
    }

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
    public function update($id, UpdateChannelCdnRequest $request): RedirectResponse
    {
        $cdn = ChannelCdn::findOrFail($id);

        UpdateChannelCdnAction::run($cdn, $request->validated());

        return redirect()->route('list_channel_cdn');
    }

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id): RedirectResponse
    {
        DeleteChannelCdnAction::run(ChannelCdn::findOrFail($id));

        return redirect()->route('list_channel_cdn');
    }

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list()
    {

        if (class_exists(IPTVCdnCustomer::class)) {
            $data['list'] = IPTVCdnCustomer::all();
        } else {
            $data['list'] = ChannelCdn::all();
        }

        $data['url_cdn'] = IPTVConfig::get('URL_CDN');
        $data['donwload'] = IPTVConfig::get('DOWNLOAD_FILE');

        return view('channel_cdn_list', $data);
    }
}
