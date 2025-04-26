<?php
Route::group([
    'middleware' => ['web', 'iptv_locale'],
	],
    function(){
        Route::get('dashboard', 'FelipeMateus\IPTVCore\Controllers\DashboardController@view')->name('dashboard');
        Route::get('iptv/config', 'FelipeMateus\IPTVCore\Controllers\ConfigController@config')->name('config');
        Route::post('iptv/config','FelipeMateus\IPTVCore\Controllers\ConfigController@configSave')->name('config_save');
    }
);
