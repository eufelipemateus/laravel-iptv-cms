<?php

namespace App\Providers;


use FelipeMateus\IPTVCore\Helpers\IPTVProviderBase;
use Illuminate\Routing\Router;


class AppServiceProvider extends IPTVProviderBase
{

    public function boot()
    {
        $this->loadMenusFrom(__DIR__ . '/resources/menu');

    }

}
