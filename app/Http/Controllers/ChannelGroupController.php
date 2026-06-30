<?php

namespace App\Http\Controllers;

use App\Actions\ChannelGroups\DeleteChannelGroupAction;
use App\Actions\ChannelGroups\StoreChannelGroupAction;
use App\Actions\ChannelGroups\UpdateChannelGroupAction;
use App\Http\Requests\ChannelGroupRequest;
use App\Http\Requests\DeleteChannelGroupRequest;
use App\Models\ChannelGroup;
use Illuminate\Http\RedirectResponse;

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
     * @return view -> IPTV::group
     */
    public function new()
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
    public function show($id)
    {
        $data['Group'] = ChannelGroup::findOrFail($id);

        return view('channel_group', $data);
    }

    /**
     * Update group in database
     *
     * @param id from group
     * @return redirect -> list_channel_group
     */
    public function update($id, ChannelGroupRequest $request): RedirectResponse
    {
        $group = ChannelGroup::findOrFail($id);
        UpdateChannelGroupAction::run($group, $request->validated());

        return redirect()->route('list_channel_group');
    }

    /**
     * Delete group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function delete(DeleteChannelGroupRequest $request): RedirectResponse
    {
        DeleteChannelGroupAction::run(ChannelGroup::findOrFail($request->id()));

        return redirect()->route('list_channel_group');
    }

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function list()
    {
        $data['list'] = ChannelGroup::get();

        return view('channel_group_list', $data);
    }
}
