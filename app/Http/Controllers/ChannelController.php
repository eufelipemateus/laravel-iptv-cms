<?php

namespace App\Http\Controllers;

use App\Actions\Channels\DeleteChannelAction;
use App\Actions\Channels\StoreChannelAction;
use App\Actions\Channels\UpdateChannelAction;
use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\ChannelGroup;
use App\Models\ChannelUrl;
use App\Models\IPTVConfig;
use Illuminate\Http\RedirectResponse;

class ChannelController extends Controller
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
        $data['Groupslist'] = ChannelGroup::get();
        $data['radio_stream'] = IPTVConfig::get('RADIO_STREAM');

        return view('channel', $data);
    }

    /**
     * Show page from channel with id.
     *
     * @param  $id  - channewl id
     * @return view -> IPTV:chanel
     */
    public function show($id)
    {
        $data['Channel'] = Channel::findOrFail($id);
        $data['Groupslist'] = ChannelGroup::get();
        $data['Cdnslist'] = ChannelCdn::all();
        $data['urls'] = ChannelUrl::where('iptv_channel_id', $id)->get();
        $data['radio_stream'] = IPTVConfig::get('RADIO_STREAM');

        return view('channel', $data);
    }

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(StoreChannelRequest $request): RedirectResponse
    {
        StoreChannelAction::run(
            $request->validated(),
            $request->file('image'),
            $request->boolean('radio'),
        );

        return redirect()->route('list_channel');
    }

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
    public function update($id, UpdateChannelRequest $request): RedirectResponse
    {
        $channel = Channel::findOrFail($id);

        UpdateChannelAction::run(
            $channel,
            $request->validated(),
            $request->file('image'),
            $request->boolean('radio'),
        );

        return redirect()->route('list_channel');
    }

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id): RedirectResponse
    {
        DeleteChannelAction::run(Channel::findOrFail($id));

        return redirect()->route('list_channel');
    }

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list()
    {
        $data['list'] = Channel::getList();

        return view('channel_list', $data);
    }
}
