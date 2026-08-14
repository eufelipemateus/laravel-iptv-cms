<?php

use App\Http\Controllers\ChannelCdnController;
use App\Http\Controllers\ChannelController;
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

use App\Http\Controllers\ChannelGroupController;
use App\Http\Controllers\ChannelListM3UController;
use App\Http\Controllers\ChannelUrlController;
use App\Http\Controllers\ConfigController;
use App\Http\Controllers\CustomerChannelsM3UController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CustomerPlanAdditionalController;
use App\Http\Controllers\CustomerPlanController;
use App\Http\Controllers\CustomerPlanGroupController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoVodeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:web')->name('login.store');
    Route::get('convite/{token}', [AuthController::class, 'invitation'])->name('invitation.show');
    Route::post('convite/{token}', [AuthController::class, 'acceptInvitation'])->middleware('throttle:web')->name('invitation.accept');
});

Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::group(
    [
        'middleware' => ['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'],
    ],
    function () {
        Route::get('dashboard', [DashboardController::class, 'view'])->name('dashboard');
    }
);

Route::group(
    [
        'middleware' => ['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'],
    ],
    function () {
        Route::get('iptv/config', [ConfigController::class, 'config'])->name('config');
        Route::post('iptv/config', [ConfigController::class, 'configSave'])->name('config_save');
    }
);

Route::middleware(['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'])->prefix('users')->group(function () {
    Route::get('me', [UserController::class, 'profile'])->name('user.profile');

});

Route::middleware(['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'])->prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'list'])->name('list_user');
    Route::get('invite', [UserController::class, 'inviteForm'])->name('users.invite');
    Route::post('invite', [UserController::class, 'invite'])->name('users.invite.store');
    Route::get('{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('{user}', [UserController::class, 'update'])->name('users.update');
});
// Channel Routes
Route::group([
    'prefix' => 'public/m3u8',
    'middleware' => ['api', 'public_cdn'],
],
    function () {
        Route::get('/{slug}', [ChannelListM3UController::class, 'show'])->name('cdn-playslit');
    });

Route::group([
    'middleware' => ['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'],
],
    function () {
        Route::prefix('channel')->group(function () {
            Route::get('list', [ChannelController::class, 'list'])->name('list_channel');
            Route::get('add', [ChannelController::class, 'new'])->name('add_channel');
            Route::post('add', [ChannelController::class, 'create'])->name('create_channel');
            Route::get('/{id}', [ChannelController::class, 'show'])->name('show_channel');
            Route::post('/{id}', [ChannelController::class, 'update'])->name('update_channel');
            Route::post('/del/{id}', [ChannelController::class, 'delete'])->name('delete_channel');
        });

        Route::prefix('group')->group(function () {
            Route::get('/list', [ChannelGroupController::class, 'list'])->name('list_channel_group');

            Route::get('/add', [ChannelGroupController::class, 'new'])->name('add_channel_group');
            Route::post('/add', [ChannelGroupController::class, 'create'])->name('create_channel_group');

            Route::get('/{id}', [ChannelGroupController::class, 'show'])->name('show_channel_group');

            Route::post('/{id}', [ChannelGroupController::class, 'update'])->name('update_channel_group');
            Route::post('/del/{id}', [ChannelGroupController::class, 'delete'])->name('delete_channel_group');
        });

        Route::prefix('cdn')->group(function () {
            Route::get('/list', [ChannelCdnController::class, 'list'])->name('list_channel_cdn');

            Route::get('/add', [ChannelCdnController::class, 'new'])->name('add_channel_cdn');
            Route::post('/add', [ChannelCdnController::class, 'create'])->name('create_channel_cdn');

            Route::get('/{id}', [ChannelCdnController::class, 'show'])->name('show_channel_cdn');
            Route::post('/{id}', [ChannelCdnController::class, 'update'])->name('update_channel_cdn');

            Route::post('/del/{id}', [ChannelCdnController::class, 'delete'])->name('delete_channel_cdn');
        });

        Route::prefix('url')->group(function () {
            Route::post('/add', [ChannelUrlController::class, 'create'])->name('create_channel_url');
            Route::post('/{id}', [ChannelUrlController::class, 'update'])->name('update_channel_url');
            Route::post('/del/{id}', [ChannelUrlController::class, 'delete'])->name('delete_channel_url');
        });
    });

// IPTV Customers Routes
if (config('modules.customer.enabled', true)) {
    Route::group([
        'prefix' => 'client/m3u8',
        'middleware' => ['api', 'client'],
    ],
        function () {
            Route::get('/{slug}', [CustomerChannelsM3UController::class, 'show'])->name('client-playlist');
        });
}

if (config('modules.customer.enabled', true)) {
    Route::group([
        'middleware' => ['web', 'auth', 'admin', 'iptv_locale', 'throttle:web'],
    ],
        function () {
            Route::prefix('plan')->group(function () {
                Route::get('/list', [CustomerPlanController::class, 'list'])->name('list_customer_plan');

                Route::get('/add', [CustomerPlanController::class, 'new'])->name('add_customer_plan');
                Route::post('/add', [CustomerPlanController::class, 'create'])->name('create_customer_plan');

                Route::get('/{id}', [CustomerPlanController::class, 'show'])->name('show_customer_plan');
                Route::post('/{id}', [CustomerPlanController::class, 'update'])->name('update_customer_plan');

                Route::post('/del/{id}', [CustomerPlanController::class, 'delete'])->name('delete_customer_plan');

                Route::post('/{plan_id}/group/add', [CustomerPlanGroupController::class, 'add'])->name('add_group_customer_plan');
                Route::post('/{plan_id}/group/delete', [CustomerPlanGroupController::class, 'delete'])->name('delete_group_customer_plan');

            });

            Route::prefix('customer')->group(function () {
                Route::get('list', [CustomerController::class, 'list'])->name('list_customer');
                Route::get('add', [CustomerController::class, 'new'])->name('add_customer');
                Route::post('add', [CustomerController::class, 'create'])->name('create_customer');
                Route::get('/{id}', [CustomerController::class, 'show'])->name('show_customer');
                Route::post('/{id}', [CustomerController::class, 'update'])->name('update_customer');
                Route::post('/del/{id}', [CustomerController::class, 'delete'])->name('delete_customer');

                Route::post('/{customer_id}/plan_additional/add', [CustomerPlanAdditionalController::class, 'add'])->name('add_additional');
                Route::post('/{customer_id}/plan_additional/del', [CustomerPlanAdditionalController::class, 'del'])->name('del_additional');

                Route::get('/{customer_id}/invoces/new', [InvoceController::class, 'new'])->name('new_customer_invoce');
                Route::post('/{customer_id}/invoces/new', [InvoceController::class, 'create'])->name('create_customer_invoce');
                Route::post('/{customer_id}/invoces/{id}/pay', [InvoceController::class, 'pay'])->name('pay_customer_invoce');
                Route::post('/{customer_id}/invoces/{id}/cancel', [InvoceController::class, 'cancel'])->name('cancel_customer_invoce');

            });

            // Route::get('/pay/{cod}/{invoce_id}', 'FelipeMateus\IPTVCustomers\Controllers\PayController@checkout')->name('pay');
        }
    );
}

Route::group([
    'middleware' => ['web', 'vod.enabled', 'auth', 'admin', 'iptv_locale', 'throttle:web'],
], function () {
    Route::prefix('vods')->group(function () {
        Route::get('list', [VideoVodeController::class, 'list'])->name('vods.list');
        Route::get('new', [VideoVodeController::class, 'new'])->name('vods.new');
        Route::post('new', [VideoVodeController::class, 'store'])->name('vods.store');
        Route::get('edit/{id}', [VideoVodeController::class, 'edit'])->name('vods.edit');
        Route::post('edit/{id}', [VideoVodeController::class, 'update'])->name('vods.update');
        Route::get('play/{id}', [VideoVodeController::class, 'stream'])->name('vods.stream');
        Route::delete('delete/{id}', [VideoVodeController::class, 'delete'])->name('vods.delete');
    });
});
