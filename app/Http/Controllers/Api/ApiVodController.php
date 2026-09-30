<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            ->paginate(min(max((int) $request->input('per_page', 15), 1), 100));

        return VodListResource::collection($vods);
    }

    public function show(string $id)
    {
        $vod = IPTVVodVideo::withVideo()
            ->whereIdentifier($id)
            ->firstOrFail();

        return new VodListResource($vod);
    }

    public function playback(string $id)
    {
        $vod = IPTVVodVideo::withVideo()
            ->whereIdentifier($id)
            ->firstOrFail();

        return $this->assets->responseFor($vod);
    }
}
