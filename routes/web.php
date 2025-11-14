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
