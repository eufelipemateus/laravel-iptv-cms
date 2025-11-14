<?php

namespace FelipeMateus\IPTVCore;

use FelipeMateus\IPTVCore\Helpers\IPTVProviderBase;
use FelipeMateus\IPTVCore\Helpers\IPTVMenu;
use FelipeMateus\IPTVCore\Helpers\IPTVDashboard;
class IPTVProvider extends IPTVProviderBase {


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        $this->registerDashboard();

        $this->publishes([
            __DIR__.'/resources/assets' => public_path('assets'),
        ],"public");
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->singleton('iptv-menu', function(){
            return new IPTVMenu();
        });
        $this->app->singleton('iptv-dashboard', function(){
            return new IPTVDashboard();
        });
    }



    /**
     * Regoster Dashboard card
     *
     * @return void
     */
    private function registerDashboard(){
        // config dash
    }

}
