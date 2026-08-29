<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChannelCdnController;
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

use App\Http\Controllers\ChannelController;
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
use App\Http\Controllers\EpgSourceController;
use App\Http\Controllers\EpgXmlController;
use App\Http\Controllers\InvoceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VideoVodeController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::get('epg.xml', [EpgXmlController::class, 'public'])->middleware(['api', 'epg.enabled'])->name('epg.public');
Route::middleware(['guest'])->group(function () {
    Route::get('login', [AuthController::class, 'create'])->name('login');
    Route::post('login', [AuthController::class, 'store'])->middleware('throttle:web')->name('login.store');
    Route::get('convite/{token}', [AuthController::class, 'invitation'])->name('invitation.show');
    Route::post('convite/{token}', [AuthController::class, 'acceptInvitation'])->middleware('throttle:web')->name('invitation.accept');
});

Route::post('logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

Route::group(
    [
        'middleware' => ['web', 'auth', 'active', 'iptv_locale', 'throttle:web'],
    ],
    function () {
        Route::get('dashboard', [DashboardController::class, 'view'])->name('dashboard');
    }
);

Route::group(
    [
        'middleware' => ['web', 'auth', 'active', 'admin', 'iptv_locale', 'throttle:web'],
    ],
    function () {
        Route::get('iptv/config', [ConfigController::class, 'config'])->name('config');
        Route::post('iptv/config', [ConfigController::class, 'configSave'])->name('config_save');
    }
);

Route::middleware(['web', 'auth', 'active', 'iptv_locale', 'throttle:web'])->prefix('users')->group(function () {
    Route::get('me', [UserController::class, 'profile'])->name('user.profile');

});

Route::middleware(['web', 'auth', 'active', 'admin', 'iptv_locale', 'throttle:web'])->prefix('users')->group(function () {
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
    'middleware' => ['web', 'auth', 'active', 'iptv_locale', 'throttle:web'],
],
    function () {
        Route::prefix('channel')->group(function () {
            Route::get('list', [ChannelController::class, 'list'])->name('list_channel');
            Route::get('add', [ChannelController::class, 'new'])->name('add_channel');
            Route::post('add', [ChannelController::class, 'create'])->name('create_channel');
            Route::get('/{channel}', [ChannelController::class, 'show'])->name('show_channel');
            Route::post('/{channel}', [ChannelController::class, 'update'])->name('update_channel');
            Route::post('/del/{channel}', [ChannelController::class, 'delete'])->name('delete_channel');
        });

        Route::prefix('group')->group(function () {
            Route::get('/list', [ChannelGroupController::class, 'list'])->name('list_channel_group');

            Route::get('/add', [ChannelGroupController::class, 'new'])->name('add_channel_group');
            Route::post('/add', [ChannelGroupController::class, 'create'])->name('create_channel_group');

            Route::get('/{channelGroup}', [ChannelGroupController::class, 'show'])->name('show_channel_group');

            Route::post('/{channelGroup}', [ChannelGroupController::class, 'update'])->name('update_channel_group');
            Route::post('/del/{channelGroup}', [ChannelGroupController::class, 'delete'])->name('delete_channel_group');
        });

        Route::prefix('cdn')->group(function () {
            Route::get('/list', [ChannelCdnController::class, 'list'])->name('list_channel_cdn');

            Route::get('/add', [ChannelCdnController::class, 'new'])->name('add_channel_cdn');
            Route::post('/add', [ChannelCdnController::class, 'create'])->name('create_channel_cdn');

            Route::get('/{channelCdn}', [ChannelCdnController::class, 'show'])->name('show_channel_cdn');
            Route::post('/{channelCdn}', [ChannelCdnController::class, 'update'])->name('update_channel_cdn');

            Route::post('/del/{channelCdn}', [ChannelCdnController::class, 'delete'])->name('delete_channel_cdn');
        });

        Route::prefix('url')->group(function () {
            Route::post('/add', [ChannelUrlController::class, 'create'])->name('create_channel_url');
            Route::post('/{channelUrl}', [ChannelUrlController::class, 'update'])->name('update_channel_url');
            Route::post('/del/{channelUrl}', [ChannelUrlController::class, 'delete'])->name('delete_channel_url');
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

Route::get('client/epg/{slug}.xml', [EpgXmlController::class, 'customer'])
    ->middleware(['api', 'customer.enabled', 'epg.enabled', 'client'])
    ->name('epg.customer');

Route::middleware(['web', 'auth', 'active', 'admin', 'iptv_locale', 'throttle:web', 'epg.enabled'])
    ->prefix('epg')->name('epg.')->group(function () {
        Route::get('sources', [EpgSourceController::class, 'index'])->name('sources.index');
        Route::get('sources/create', [EpgSourceController::class, 'create'])->name('sources.create');
        Route::post('sources', [EpgSourceController::class, 'store'])->name('sources.store');
        Route::get('sources/{source}/edit', [EpgSourceController::class, 'edit'])->name('sources.edit');
        Route::put('sources/{source}', [EpgSourceController::class, 'update'])->name('sources.update');
        Route::delete('sources/{source}', [EpgSourceController::class, 'destroy'])->name('sources.destroy');
        Route::post('sources/{source}/sync', [EpgSourceController::class, 'sync'])->name('sources.sync');
        Route::get('channels', [EpgSourceController::class, 'channels'])->name('channels.index');
        Route::get('channels/search', [EpgSourceController::class, 'searchChannels'])->name('channels.search');
        Route::get('programmes', [EpgSourceController::class, 'programmes'])->name('programmes.index');
    });

Route::group([
    'middleware' => ['web', 'customer.enabled', 'auth', 'active', 'iptv_locale', 'throttle:web'],
],
    function () {
        Route::prefix('plan')->group(function () {
            Route::get('/list', [CustomerPlanController::class, 'list'])->name('list_customer_plan');

            Route::get('/add', [CustomerPlanController::class, 'new'])->name('add_customer_plan');
            Route::post('/add', [CustomerPlanController::class, 'create'])->name('create_customer_plan');

            Route::get('/{customerPlan}', [CustomerPlanController::class, 'show'])->name('show_customer_plan');
            Route::post('/{customerPlan}', [CustomerPlanController::class, 'update'])->name('update_customer_plan');

            Route::post('/del/{customerPlan}', [CustomerPlanController::class, 'delete'])->name('delete_customer_plan');

            Route::post('/{customerPlan}/group/add', [CustomerPlanGroupController::class, 'add'])->name('add_group_customer_plan');
            Route::post('/{customerPlan}/group/delete', [CustomerPlanGroupController::class, 'delete'])->name('delete_group_customer_plan');

        });

        Route::prefix('customer')->group(function () {
            Route::get('list', [CustomerController::class, 'list'])->name('list_customer');
            Route::get('add', [CustomerController::class, 'new'])->name('add_customer');
            Route::post('add', [CustomerController::class, 'create'])->name('create_customer');
            Route::get('/{customer}', [CustomerController::class, 'show'])->name('show_customer');
            Route::post('/{customer}', [CustomerController::class, 'update'])->name('update_customer');
            Route::post('/del/{customer}', [CustomerController::class, 'delete'])->name('delete_customer');

            Route::post('/{customer}/plan_additional/add', [CustomerPlanAdditionalController::class, 'add'])->name('add_additional');
            Route::post('/{customer}/plan_additional/del', [CustomerPlanAdditionalController::class, 'del'])->name('del_additional');

            Route::get('/{customer}/invoces/new', [InvoceController::class, 'new'])->name('new_customer_invoce');
            Route::post('/{customer}/invoces/new', [InvoceController::class, 'create'])->name('create_customer_invoce');
            Route::post('/{customer}/invoces/{customerInvoce}/pay', [InvoceController::class, 'pay'])->name('pay_customer_invoce');
            Route::post('/{customer}/invoces/{customerInvoce}/cancel', [InvoceController::class, 'cancel'])->name('cancel_customer_invoce');

        });

        // Route::get('/pay/{cod}/{invoce_id}', 'FelipeMateus\IPTVCustomers\Controllers\PayController@checkout')->name('pay');
    }
);

Route::group([
    'middleware' => ['web', 'vod.enabled', 'auth', 'active', 'iptv_locale', 'throttle:web'],
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
