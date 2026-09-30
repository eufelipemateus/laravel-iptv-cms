<?php

namespace App\Http\Controllers;

use App\Actions\ChannelUrls\DeleteChannelUrlAction;
use App\Actions\ChannelUrls\StoreChannelUrlAction;
use App\Actions\ChannelUrls\UpdateChannelUrlAction;
use App\Http\Requests\ChannelUrlRequest;
use App\Models\ChannelUrl;
use Illuminate\Http\RedirectResponse;

class ChannelUrlController extends Controller
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
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function create(ChannelUrlRequest $request): RedirectResponse
    {
        $data = $request->validated();
        StoreChannelUrlAction::run($data);

        return redirect()->route('show_channel', ['channel' => $data['iptv_channel_id']]);
    }

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
    public function update(ChannelUrl $channelUrl, ChannelUrlRequest $request): RedirectResponse
    {
        $data = $request->validated();
        UpdateChannelUrlAction::run($channelUrl, $data);

        return redirect()->route('show_channel', ['channel' => $data['iptv_channel_id']]);
    }

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete(ChannelUrl $channelUrl): RedirectResponse
    {
        $channelId = $channelUrl->iptv_channel_id;
        DeleteChannelUrlAction::run($channelUrl);

        return redirect()->route('show_channel', ['channel' => $channelId]);
    }
}
