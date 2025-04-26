<?php

namespace FelipeMateus\IPTVChannels;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use FelipeMateus\IPTVChannels\Middleware\PublicCdnMiddleware;
use FelipeMateus\IPTVCore\Helpers\IPTVProviderBase;
use FelipeMateus\IPTVChannels\Dashs\Channels;
use FelipeMateus\IPTVChannels\Dashs\Groups;
use FelipeMateus\IPTVChannels\Dashs\Cdns;

class IPTVProvider extends IPTVProviderBase {


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        $this->registerMidleware();
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
        $this->loadJSONTranslationsFrom(__DIR__.'/resources/translations');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'IPTV');
		$this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadMenusFrom(__DIR__.'/resources/menu');
        $this->registerDashboard();
    }



    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Register Midleware
     *
     * @return void
     */
    private function registerMidleware(){
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('public_cdn', PublicCdnMiddleware::class);
    }

     /**
     * Regoster Dashboard card
     *
     * @return void
     */
    private function registerDashboard(){
        $this->loadDashFrom(Channels::class);
        $this->loadDashFrom(Groups::class);
        $this->loadDashFrom(Cdns::class);
    }
}
