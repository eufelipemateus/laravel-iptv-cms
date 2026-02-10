<?php

namespace App\Helpers;

class IPTVDashboard {

    private $dashs = [];


    public function add($dash){
        array_push($this->dashs,$dash);
    }

    public function view(){
        return view('dash', ['dashs' =>  $this->dashs]);
    }

}
