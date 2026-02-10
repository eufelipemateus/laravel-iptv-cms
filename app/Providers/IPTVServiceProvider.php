<?php

namespace App\Providers;


use App\Helpers\IPTVProviderBase;
use Illuminate\Routing\Router;
use App\Helpers\IPTVMenu;
use App\Helpers\IPTVDashboard;
use App\Dashs\Channels;
use App\Dashs\ChannelGroups;
use App\Dashs\ChannelCdns;


class IPTVServiceProvider extends IPTVProviderBase
{

    public function boot()
    {
        $this->loadMenusFrom(base_path('resources/menu'));
        $this->loadJSONTranslationsFrom(base_path('resources/translations'));
        $this->registerDashboard();

    }


    public function register()
    {
        //
        $this->app->singleton('iptv-menu', function () {
            return new IPTVMenu;
        });
        $this->app->singleton('iptv-dashboard', function () {
            return new IPTVDashboard;
        });
    }


    /**
     * Register Dashboard card
     *
     * @return void
     */
    private function registerDashboard(){
        $this->loadDashFrom(Channels::class);
        $this->loadDashFrom(ChannelGroups::class);
        $this->loadDashFrom(ChannelCdns::class);
    }
}
