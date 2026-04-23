<?php

use FelipeMateus\IPTVCore\Helpers\IPTVDashBase;

class DummyClass extends IPTVDashBase {

    public static  $title = "Example Dash";

    public static function view(){
        return parent::view();
    }

}
