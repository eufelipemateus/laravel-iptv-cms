<?php

namespace FelipeMateus\IPTVCustomers;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use FelipeMateus\IPTVCustomers\Middleware\CustomerMiddleware;
use FelipeMateus\IPTVCore\Helpers\IPTVProviderBase;
use FelipeMateus\IPTVCustomers\Dashs\Customers;
use FelipeMateus\IPTVCustomers\Dashs\Plans;
use FelipeMateus\IPTVCustomers\Commands\GenerateInvoces;
use Illuminate\Console\Scheduling\Schedule;

class IPTVProvider extends IPTVProviderBase
{


    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMidleware();
        $this->loadMigrationsFrom(__DIR__.'/database/migrations/');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'IPTV');
		$this->loadRoutesFrom(__DIR__.'/routes.php');
        $this->loadJSONTranslationsFrom(__DIR__.'/resources/translations');
        $this->loadMenusFrom(__DIR__.'/resources/menu');
        $this->registerDashboard();
        $this->registerCommands();
        $this->registerSchedule();
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
        $router->aliasMiddleware('client', CustomerMiddleware::class);
    }

    /**
     * Regoster Dashboard card
     *
     * @return void
     */
    private function registerDashboard(){
        $this->loadDashFrom(Customers::class);
        $this->loadDashFrom(Plans::class);
    }

    /**
     * Register Commands
     *
     * @return void
     */
    private function registerCommands(){
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateInvoces::class,
            ]);
        }
    }

    /**
     * Register Schedule
     *
     * @return void
     */
    private function  registerSchedule (){
        $this->app->booted(function () {
            $schedule = app(Schedule::class);
            $schedule->command('invoce:month')->monthlyOn(1, '02:00');
        });
    }

}
