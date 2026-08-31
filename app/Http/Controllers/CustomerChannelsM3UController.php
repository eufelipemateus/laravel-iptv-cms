<?php

namespace App\Http\Controllers;

use App\Actions\Customers\GetCustomerPlaylistDataAction;
use App\Http\Requests\CustomerChannelsM3URequest;
use App\Services\OperationModeService;

class CustomerChannelsM3UController extends Controller
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /**
     *  This fucntion return file M3U to list to player
     *
     * @return response
     */
    public function show(CustomerChannelsM3URequest $request)
    {
        if (! $this->operationModeService->isM3u8()) {
            abort(404);
        }

        $slug = $request->slug();
        $customer = $request->customer();

        $data = GetCustomerPlaylistDataAction::run($customer, $slug);

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
