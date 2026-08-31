<?php

namespace App\Http\Controllers;

use App\Actions\Channels\GetPublicPlaylistDataAction;
use App\Services\OperationModeService;

class ChannelListM3UController extends Controller
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /**
     *  This fucntion return file M3U to list to player
     *
     * @return response
     */
    public function show(string $slug)
    {
        if (! $this->operationModeService->isM3u8()) {
            abort(404);
        }

        $data = GetPublicPlaylistDataAction::run($slug);

        $response = response()->view('list_M3U', $data, 200);
        $response->header('Content-Type', 'text/plain; charset=utf-8');

        if ($data['download']) {
            $response->header('Cache-Control', 'public');
            $response->header('Content-Description', 'File Transfer');
            $response->header('Content-Disposition', 'attachment; filename='.$slug.'.m3u8');
        }

        return $response;
    }
}
