<?php

namespace App\Http\Controllers;

use App\Http\Resources\VodListResource;
use App\Models\IPTVVodVideo;
use App\Services\Vod\VodAssetService;
use Illuminate\Http\Request;

class ApiVodController extends Controller
{
    public function __construct(private VodAssetService $assets) {}

    public function list(Request $request)
    {
        $vods = IPTVVodVideo::query()
            ->withVideo()
            ->search($request->input('search'))
            ->orderBy('name')
            ->paginate((int) $request->input('per_page', 15));

        return VodListResource::collection($vods);
    }

    public function show($id)
    {
        $vod = IPTVVodVideo::query()
            ->withVideo()
            ->where(fn ($query) => $query->where('id', $id)->orWhere('slug', $id)->orWhere('uuid', $id))
            ->firstOrFail();

        return new VodListResource($vod);
    }

    public function playback($id)
    {
        $vod = IPTVVodVideo::query()
            ->withVideo()
            ->where(fn ($query) => $query->where('id', $id)->orWhere('slug', $id)->orWhere('uuid', $id))
            ->firstOrFail();

        return $this->assets->responseFor($vod);
    }
}
