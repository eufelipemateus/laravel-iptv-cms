<?php

namespace App\Http\Controllers;

use App\Actions\Config\UpdateConfigAction;
use App\Helpers\Locale;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\IPTVConfig;
use Illuminate\Http\RedirectResponse;

class ConfigController extends Controller
{
    /**
     * Show config page.
     *
     * @return view -> IPTV::config
     */
    public function config()
    {
        $data['config_list'] = IPTVConfig::getAllBoleanSettings();
        $data['locales'] = Locale::getList();
        $data['current_locate'] = IPTVConfig::get('CURRENT_LOCALE', 'br');
        $data['inputs'] = IPTVConfig::getAllStringSettings();

        return view('config', $data);
    }

    /**
     * Update config .
     *
     * @return redirect -> show configs
     */
    public function configSave(UpdateConfigRequest $request): RedirectResponse
    {
        UpdateConfigAction::run($request);

        return redirect()->route('config');
    }
}
