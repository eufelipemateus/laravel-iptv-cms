<?php

namespace App\Helpers;

use App\Facades\Dashboard;
use App\Facades\Menu;
use Illuminate\Support\ServiceProvider;

class IPTVProviderBase extends ServiceProvider
{
    protected function loadMenusFrom($path)
    {
        $json = $path.'.json';
        $menu = json_decode(file_get_contents($json), true);

        Menu::add($menu);
    }

    protected function loadDashFrom($dash)
    {
        Dashboard::add($dash);
    }
}
