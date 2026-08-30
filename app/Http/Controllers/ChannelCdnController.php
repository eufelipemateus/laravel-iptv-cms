<?php

namespace App\Http\Controllers;

use App\Actions\ChannelCdns\DeleteChannelCdnAction;
use App\Actions\ChannelCdns\GetChannelCdnListDataAction;
use App\Actions\ChannelCdns\StoreChannelCdnAction;
use App\Actions\ChannelCdns\UpdateChannelCdnAction;
use App\Http\Requests\StoreChannelCdnRequest;
use App\Http\Requests\UpdateChannelCdnRequest;
use App\Models\ChannelCdn;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChannelCdnController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    

    /**
     * Show new channewl page.
     *
     * @return View -> IPTV:chanel
     */
    public function new(): View
    {
        return view('channel_cdn');
    }

    /**
     * Show page from channel with id.
     *
     * @param  $id  - channewl id
     * @return View -> IPTV:chanel
     */
    public function show(ChannelCdn $channelCdn): View
    {
        $data['cdn'] = $channelCdn;

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
    public function update(ChannelCdn $channelCdn, UpdateChannelCdnRequest $request): RedirectResponse
    {
        UpdateChannelCdnAction::run($channelCdn, $request->validated());

        return redirect()->route('list_channel_cdn');
    }

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete(ChannelCdn $channelCdn): RedirectResponse
    {
        DeleteChannelCdnAction::run($channelCdn);

        return redirect()->route('list_channel_cdn');
    }

    /**
     * Return a channel List from database.
     *
     * @return View -> IPTV::channel_list
     */
    public function list(): View
    {

        return view('channel_cdn_list', GetChannelCdnListDataAction::run());
    }
}
