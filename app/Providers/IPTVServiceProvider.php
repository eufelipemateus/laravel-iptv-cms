<?php

namespace App\Providers;

use App\Dashs\ChannelCdns;
use App\Dashs\ChannelGroups;
use App\Dashs\Channels;
use App\Dashs\CustomerPlans;
use App\Dashs\Customers;
use App\Dashs\Epg;
use App\Dashs\Vods;
use App\Helpers\Dashboard;
use App\Helpers\IPTVProviderBase;
use App\Helpers\Menu;

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
            return new Menu;
        });
        $this->app->singleton('iptv-dashboard', function () {
            return new Dashboard;
        });
    }

    /**
     * Register Dashboard card
     *
     * @return void
     */
    private function registerDashboard()
    {
        $this->loadDashFrom(Channels::class);
        $this->loadDashFrom(ChannelGroups::class);
        $this->loadDashFrom(ChannelCdns::class);
        if (config('modules.customer.enabled', true)) {
            $this->loadDashFrom(Customers::class);
            $this->loadDashFrom(CustomerPlans::class);
        }

        if (config('modules.vod.enabled', false)) {
            $this->loadDashFrom(Vods::class);
        }

        if (config('modules.epg.enabled', false)) {
            $this->loadDashFrom(Epg::class);
        }
    }
}
