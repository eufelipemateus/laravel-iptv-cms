<?php

namespace App\Http\Controllers;

use App\Actions\ChannelGroups\DeleteChannelGroupAction;
use App\Actions\ChannelGroups\ListChannelGroupsAction;
use App\Actions\ChannelGroups\StoreChannelGroupAction;
use App\Actions\ChannelGroups\UpdateChannelGroupAction;
use App\Http\Requests\ChannelGroupRequest;
use App\Models\ChannelGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ChannelGroupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // $this->middleware('auth');
    }

    /**
     * Return new page _blank.
     *
     * @return View -> IPTV::group
     */
    public function new(): View
    {
        return view('channel_group');
    }

    /**
     * Create new channel in database.
     *
     * @return redirect -> list_channel_group
     */
    public function create(ChannelGroupRequest $request): RedirectResponse
    {
        StoreChannelGroupAction::run($request->validated());

        return redirect()->route('list_channel_group');
    }

    /**
     * Return a page with group from database.
     *
     * @param id -> from group
     * @return redirect -> list_channel_group
     */
    public function show(ChannelGroup $channelGroup): View
    {
        $data['Group'] = $channelGroup;

        return view('channel_group', $data);
    }

    /**
     * Update group in database
     *
     * @param id from group
     * @return redirect -> list_channel_group
     */
    public function update(ChannelGroup $channelGroup, ChannelGroupRequest $request): RedirectResponse
    {
        UpdateChannelGroupAction::run($channelGroup, $request->validated());

        return redirect()->route('list_channel_group');
    }

    /**
     * Delete group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function delete(ChannelGroup $channelGroup): RedirectResponse
    {
        DeleteChannelGroupAction::run($channelGroup);

        return redirect()->route('list_channel_group');
    }

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function list(): View
    {
        $data['list'] = ListChannelGroupsAction::run();

        return view('channel_group_list', $data);
    }
}
