<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ChannelGroup;

class ChannelGroupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Return new page _blank.
     *
     * @return view -> IPTV::group
     */
	public function new(){
		return view("channel_group");
	}

    /**
     * Create new channel in database.
     *
     * @return redirect -> list_channel_group
     */
    public function create(Request $request){
		$data = $request->validate([
            'name' => 'required|string|max:60',
        ]);
		ChannelGroup::create($data);
		return redirect()->route('list_channel_group');
	}

    /**
     * Return a page with group from database.
     *
     * @param id -> from group
     * @return redirect -> list_channel_group
     */
    public function show($id){
		$data["Group"] = ChannelGroup::findOrFail($id);
		return view("channel_group",$data);
	}

    /**
     * Update group in database
     *
     * @param id from group
     * @return redirect -> list_channel_group
     */
    public function update($id,Request $request){
		$group =ChannelGroup::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:60',
        ]);
		$group->update($data);
		return redirect()->route('list_channel_group');
	}

    /**
     * Delete group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function delete($id,Request $request){
		$group =ChannelGroup::findOrFail($id);
		$group->delete();
		return redirect()->route('list_channel_group');
	}

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function list(){
		$data["list"] = ChannelGroup::get();
		return view("channel_group_list",$data);
	}

}
