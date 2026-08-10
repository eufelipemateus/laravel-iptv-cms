<?php

namespace App\Helpers;

use Illuminate\Support\ServiceProvider;
use App\Facades\Menu;
use App\Facades\Dashboard;

class IPTVProviderBase extends ServiceProvider {

    protected function loadMenusFrom($path){
        $json = $path.".json";
        $menu = json_decode(file_get_contents($json), true);

        if (is_array($menu)) {
            $menu = array_values(array_filter($menu, function (array $item): bool {
                if (! isset($item['enabled_when'])) {
                    return true;
                }

                return (bool) config($item['enabled_when'], true);
            }));
        }

        Menu::add($menu);
    }

    protected function loadDashFrom($dash){
        Dashboard::add($dash);
    }
}
