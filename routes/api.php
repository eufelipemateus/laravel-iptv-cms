<?php

use App\Http\Controllers\Api\ApiVodController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::middleware('vod.enabled')->group(function () {
    Route::get('/vods', [ApiVodController::class, 'list'])->name('api.vods.list');
    Route::get('/vods/{id}', [ApiVodController::class, 'show'])->name('api.vods.show');
    Route::get('/vods/{id}/play', [ApiVodController::class, 'playback'])->name('api.vods.playback');
});
