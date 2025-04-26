<?php

Route::group([
    'middleware' => ['web', 'iptv_locale'],
	],
	function(){

        Route::prefix('gateway')->group(function () {
            Route::get('/list', 'FelipeMateus\IPTVGatewayPayment\Controllers\GatewayController@list')->name('list_gateway');
            Route::get('/active/{gateway}', 'FelipeMateus\IPTVGatewayPayment\Controllers\GatewayController@active')->name('active_gateway');
            Route::get('/inactive/{gateway}', 'FelipeMateus\IPTVGatewayPayment\Controllers\GatewayController@inactive')->name('inactive_gateway');

        });

        Route::prefix('tax')->group(function () {
            Route::get('/list', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@list')->name('list_tax');
            Route::get('/add', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@new')->name('add_tax');
            Route::post('/add', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@create')->name('add_tax');
            Route::get('/{id}', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@show')->name('show_tax');
            Route::post('/{id}', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@update');
            Route::get('/{id}/del', 'FelipeMateus\IPTVGatewayPayment\Controllers\TaxVatController@delete')->name('delete_tax');
        });

    }
);
