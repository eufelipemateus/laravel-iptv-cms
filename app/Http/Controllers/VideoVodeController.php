<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVodRequest;
use App\Http\Requests\UpdateVodRequest;
use App\Models\IPTVVodVideo;
use App\Services\Vod\VodAssetService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VideoVodeController extends Controller
{
    public function __construct(private VodAssetService $assets) {}

    /**
     * Show new channewl page.
     *
     * @return view -> IPTV:chanel
     */
    public function new()
    {
        return view('vod');
    }

    /**
     * Show page from channel with id.
     *
     * @param  $id  - channewl id
     * @return view -> IPTV:chanel
     */
    public function edit($id)
    {
        $vod = IPTVVodVideo::findOrFail($id);

        return view('vod', ['Vod' => $vod]);
    }

    /**
     * Save new data from new channel in database.
     *
     * @return redirect -> list_channels
     */
    public function store(StoreVodRequest $request)
    {
        $vod = DB::transaction(function () use ($request) {
            $vod = IPTVVodVideo::create($this->vodData($request));

            if ($request->hasFile('file')) {
                $this->assets->store($vod, $request->file('file'));
            }

            return $vod->refresh();
        });

        return redirect()->route('vods.edit', $vod->id)->with('success', __('VOD saved successfully.'));
    }

    /**
     * Save new data from new channel in database.
     *
     * @param id from channel
     * @return redirect -> list_channels
     */
    public function update($id, UpdateVodRequest $request)
    {
        $vod = IPTVVodVideo::findOrFail($id);

        DB::transaction(function () use ($request, $vod) {
            $vod->update($this->vodData($request));

            if ($request->hasFile('file')) {
                $this->assets->store($vod, $request->file('file'));
            }
        });

        return redirect()->route('vods.edit', $vod->id)->with('success', __('VOD updated successfully.'));
    }

    /**
     * Delete channel form database.
     *
     * @param id from channel
     * @return redirect -> list_channel
     */
    public function delete($id)
    {
        $vod = IPTVVodVideo::findOrFail($id);

        DB::transaction(function () use ($vod) {
            $this->assets->delete($vod);
            $vod->delete();
        });

        return redirect()->route('vods.list')->with('success', __('VOD deleted successfully.'));
    }

    /**
     * Return a channel List from database.
     *
     * @return view -> IPTV::channel_list
     */
    public function list(Request $request)
    {
        $query = IPTVVodVideo::query()
            ->search($request->input('search'))
            ->orderByDesc('updated_at');

        return view('vods', [
            'list' => $query->paginate(15)->appends($request->query()),
            'search' => $request->input('search'),
        ]);
    }

    public function stream($id)
    {
        $vod = IPTVVodVideo::findOrFail($id);

        return $this->assets->responseFor($vod);
    }

    private function vodData(Request $request): array
    {
        return [
            'name' => $request->input('name'),
            'description' => $request->input('description'),
        ];
    }
}
