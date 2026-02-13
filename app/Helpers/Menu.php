<?php

namespace App\Helpers;

class Menu {

    private $menusitens = [];

    public function add($menu){
        if (is_array($menu) && isset($menu[0]) && is_array($menu[0])) {
            // Array de menus
            foreach ($menu as $item) {
                array_push($this->menusitens, $item);
            }
        } else {
            array_push($this->menusitens, $menu);
        }
    }

    public function view(){
        return view('menu', ['menusList' =>  $this->menusitens]);
    }
}
