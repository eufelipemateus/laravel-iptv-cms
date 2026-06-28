<?php

use FelipeMateus\IPTVCore\Helpers\DashBase;

class DummyClass extends DashBase {

    public static  $title = "Example Dash";

    public static function view(){
        return parent::view();
    }

}
