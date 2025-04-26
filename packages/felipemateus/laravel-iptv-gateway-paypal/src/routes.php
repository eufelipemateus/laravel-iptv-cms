<?php

Route::group([
    'middleware' => ['web', 'iptv_locale'],
	],
	function(){

        Route::prefix('paypal')->group(function () {
            Route::get('/config', 'FelipeMateus\IPTVPaypal\Controllers\ConfigPayPalController@config')->name('cofig_paypal');
            Route::post('/config', 'FelipeMateus\IPTVPaypal\Controllers\ConfigPayPalController@save_config');

            Route::post('/checkout', 'FelipeMateus\IPTVPaypal\Controllers\PayController@checkout')->name('checkout_paypal');

            Route::get('/cancel','FelipeMateus\IPTVPaypal\Controllers\PayController@cancelled')->name('cancelled_paypal');
            Route::get('/aprove','FelipeMateus\IPTVPaypal\Controllers\PayController@approved')->name('approved_paypal');

        });

    }
);
