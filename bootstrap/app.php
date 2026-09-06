<?php

use App\Http\Middleware\BlockWhenInstalling;
use App\Http\Middleware\CustomerMiddleware;
use App\Http\Middleware\EnsureCustomerModuleIsEnabled;
use App\Http\Middleware\EnsureEpgModuleIsEnabled;
use App\Http\Middleware\EnsureUserIsActive;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureVodModuleIsEnabled;
use App\Http\Middleware\IPTVLocaleMiddleware;
use App\Http\Middleware\PublicCdnMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->prepend(BlockWhenInstalling::class);

        $middleware->alias([
            'iptv_locale' => IPTVLocaleMiddleware::class,
            'client' => CustomerMiddleware::class,
            'public_cdn' => PublicCdnMiddleware::class,
            'active' => EnsureUserIsActive::class,
            'admin' => EnsureUserIsAdmin::class,
            'vod.enabled' => EnsureVodModuleIsEnabled::class,
            'epg.enabled' => EnsureEpgModuleIsEnabled::class,
            'customer.enabled' => EnsureCustomerModuleIsEnabled::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
