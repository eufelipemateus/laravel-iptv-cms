<?php

namespace  App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\IPTVConfig;
use App\Helpers\Locale;

class ConfigController extends Controller
{
    /**
     * Show config page.
     *
     * @return view -> IPTV::config
     */
    public function config()
    {
        $data["config_list"] = IPTVConfig::getAllBoleanSettings();
        $data['locales'] = Locale::getList();
        $data["current_locate"] = IPTVConfig::get('CURRENT_LOCALE', 'br');
        $data["inputs"] =  IPTVConfig::getAllStringSettings();
        return view("IPTV::config", $data);
    }

    /**
     * Update config .
     *
     * @return redirect -> show configs
     */
    public function configSave(Request $request ){
        $configs = IPTVConfig::getAllBoleanSettings();
        foreach ($configs as $config) {
            IPTVConfig::set(
                $config['name'],
                $request->boolean($config['name']),
                'bool'
            );
        }

        IPTVConfig::set(
            'CURRENT_LOCALE',
            $request->input('CURRENT_LOCALE'),
            'locale'
        );
        return redirect()->route('config');
    }
}
