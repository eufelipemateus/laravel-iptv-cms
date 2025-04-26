<?php
namespace FelipeMateus\IPTVPaypal\Helpers;

class Paypal {



    public static function form($price, $invoce_id){

        $data['invoce_id'] = $invoce_id;
        $data['invoce_price'] = $price;

        return view("IPTV::paypal_form", $data);
    }

}
