<?php

namespace FelipeMateus\IPTVCore\Helpers;

use Illuminate\Support\ServiceProvider;
use FelipeMateus\IPTVCore\Facades\IPTVMenu;
use FelipeMateus\IPTVCore\Facades\IPTVDashboard;

class IPTVProviderBase extends ServiceProvider {

    protected function loadMenusFrom($path){
        $json = $path.".json";
        $menu = json_decode(file_get_contents($json), true);
        IPTVMenu::add($menu);
    }

    protected function loadDashFrom($dash){
        IPTVDashboard::add($dash);
    }
}
