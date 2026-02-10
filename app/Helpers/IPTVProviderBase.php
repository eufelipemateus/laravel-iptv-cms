<?php

namespace App\Helpers;

use Illuminate\Support\ServiceProvider;
use App\Facades\IPTVMenu;
use App\Facades\IPTVDashboard;

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
