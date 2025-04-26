<?php

namespace  FelipeMateus\IPTVChannels\Controllers;

use Illuminate\Http\Request;
use FelipeMateus\IPTVChannels\Model\IPTVChannelGroup;
use FelipeMateus\IPTVCore\Controllers\CoreController;

class GroupController extends CoreController
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
		return view("IPTV::group");
	}

    /**
     * Create new channel in database.
     *
     * @return redirect -> list_group
     */
    public function create(Request $request){
		$data = $request->all();
		IPTVChannelGroup::create($data);
		return redirect()->route('list_group');
	}

    /**
     * Return a page with group from database.
     *
     * @param id -> from group
     * @return redirect -> list_group
     */
    public function show($id){
		$data["Group"] = IPTVChannelGroup::findOrFail($id);
		return view("IPTV::group",$data);
	}

    /**
     * Update group in database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function update($id,Request $request){
		$group =IPTVChannelGroup::findOrFail($id);
		$group->update($request->all());
		return redirect()->route('list_group');
	}

    /**
     * Delete group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function delete($id,Request $request){
		$group =IPTVChannelGroup::findOrFail($id);
		$group->delete();
		return redirect()->route('list_group');
	}

    /**
     * Return list group from database
     *
     * @param id from group
     * @return redirect -> list_group
     */
    public function list(){
		$data["list"] = IPTVChannelGroup::get();
		return view("IPTV::group_list",$data);
	}

}
