<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\ChannelGroupController;
use App\Http\Controllers\ChannelCdnController;
use App\Http\Controllers\ChannelUrlController;


Route::redirect('/', '/dashboard');
Route::group(
    [
        'middleware' => ['web', 'iptv_locale'],
    ],
    function () {
        Route::get('dashboard', [DashboardController::class, 'view'])->name('dashboard');
        Route::get('iptv/config', [ConfigController::class, 'config'])->name('config');
        Route::post('iptv/config', [ConfigController::class, 'configSave'])->name('config_save');
    }
);

#Channel Routes
Route::group([
    'prefix' => 'public/m3u8',
    'middleware' => ['api','public_cdn'],
	],
    function(){
        Route::get('/{slug}',"FelipeMateus\IPTVChannels\Controllers\ChannelListM3UController@show")->name("cdn-playslit");
    });

Route::group([
    'middleware' => ['web', 'iptv_locale'],
	],
	function(){
        Route::prefix('channel')->group(function () {
            Route::get('list', [ChannelController::class, 'list'])->name('list_channel');
            Route::get('add', [ChannelController::class, 'new'])->name('add_channel');
            Route::post('add', [ChannelController::class, 'create'])->name('create_channel');
            Route::get('/{id}', [ChannelController::class, 'show'])->name('show_channel');
            Route::post('/{id}', [ChannelController::class, 'update'])->name('update_channel');
            Route::get('/del/{id}', [ChannelController::class, 'delete'])->name('delete_channel');
        });


        Route::prefix('group')->group(function () {
            Route::get('/list', [ChannelGroupController::class, 'list'])->name('list_channel_group');

            Route::get('/add', [ChannelGroupController::class, 'new'])->name('add_channel_group');
            Route::post('/add', [ChannelGroupController::class, 'create'])->name('create_channel_group');

            Route::get('/{id}', [ChannelGroupController::class, 'show'])->name('show_channel_group');

            Route::post('/{id}', [ChannelGroupController::class, 'update'])->name('update_channel_group');
            Route::get('/del/{id}', [ChannelGroupController::class, 'delete'])->name('delete_channel_group');
        });


        Route::prefix('cdn')->group(function () {
            Route::get('/list', [ChannelCdnController::class, 'list'])->name('list_channel_cdn');

            Route::get('/add', [ChannelCdnController::class, 'new'])->name('add_channel_cdn');
            Route::post('/add', [ChannelCdnController::class, 'create'])->name('create_channel_cdn');


            Route::get('/{id}', [ChannelCdnController::class, 'show'])->name('show_channel_cdn');
            Route::post('/{id}', [ChannelCdnController::class, 'update'])->name('update_channel_cdn');

            Route::get('/del/{id}', [ChannelCdnController::class, 'delete'])->name('delete_channel_cdn');
        });

        Route::prefix('url')->group(function () {
            Route::post('/add', [ChannelUrlController::class, 'create'])->name('create_channel_url');
            Route::post('/{id}', [ChannelUrlController::class, 'update'])->name('update_channel_url');
            Route::get('/del/{id}', [ChannelUrlController::class, 'delete'])->name('delete_channel_url');
        });
	});
