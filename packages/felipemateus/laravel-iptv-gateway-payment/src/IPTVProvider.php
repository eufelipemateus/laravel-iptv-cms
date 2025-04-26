<?php

namespace FelipeMateus\IPTVGatewayPayment;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use FelipeMateus\IPTVCore\Helpers\IPTVProviderBase;
use FelipeMateus\IPTVGatewayPayment\Dashs\GatewayDash;

class IPTVProvider extends IPTVProviderBase {


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot(){
        //$this->registerMidleware();
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
     * Regoster Dashboard card
     *
     * @return void
     */
    private function registerDashboard(){
        $this->loadDashFrom(GatewayDash::class);
    }
}
