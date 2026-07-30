<?php

namespace App\Http\Controllers;

use App\Actions\Config\UpdateConfigAction;
use App\Helpers\Locale;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\IPTVConfig;
use App\Services\OperationModeService;
use Illuminate\Http\RedirectResponse;

class ConfigController extends Controller
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

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
        $data['inputs'] = collect(IPTVConfig::getAllStringSettings())
            ->reject(fn (array $input): bool => ($input['name'] ?? null) === OperationModeService::CONFIG_KEY)
            ->values()
            ->all();
        $data['mode'] = $this->operationModeService->current();
        $data['is_m3u8_mode'] = $this->operationModeService->isM3u8();
        $data['is_dtv3_mode'] = $this->operationModeService->isDtv3();

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
