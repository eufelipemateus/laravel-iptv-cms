<?php

namespace FelipeMateus\IPTVCore\Helpers;

class IPTVMenu {

    private $menusitens = [];

    public function add($menu){
        array_push($this->menusitens,$menu);
    }

    public function view(){
        return view('IPTV::menu', ['menusList' =>  $this->menusitens]);
    }
}
