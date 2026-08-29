<?php

namespace App\Http\Controllers;

use App\Actions\Channels\DeleteChannelAction;
use App\Actions\Channels\GetChannelFormDataAction;
use App\Actions\Channels\ListChannelsAction;
use App\Actions\Channels\StoreChannelAction;
use App\Actions\Channels\UpdateChannelAction;
use App\Http\Requests\StoreChannelRequest;
use App\Http\Requests\UpdateChannelRequest;
use App\Models\Channel;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

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
     * @return View -> IPTV:chanel
     */
    public function new(): View
    {
        return view('channel', GetChannelFormDataAction::run());
    }

    /**
     * Show page from channel with id.
     *
     * @param  $channel  - channewl id
     * @return View -> IPTV:chanel
     */
    public function show(Channel $channel): View
    {

        return view('channel', GetChannelFormDataAction::run($channel));
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
    public function update(Channel $channel, UpdateChannelRequest $request): RedirectResponse
    {
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
    public function delete(Channel $channel): RedirectResponse
    {
        DeleteChannelAction::run($channel);

        return redirect()->route('list_channel');
    }

    /**
     * Return a channel List from database.
     *
     * @return View -> IPTV::channel_list
     */
    public function list(): View
    {
        $data['list'] = ListChannelsAction::run();

        return view('channel_list', $data);
    }
}
