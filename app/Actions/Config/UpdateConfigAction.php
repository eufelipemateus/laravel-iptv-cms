<?php

namespace App\Actions\Config;

use App\Http\Requests\UpdateConfigRequest;
use App\Models\IPTVConfig;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateConfigAction
{
    use AsAction;

    public function handle(UpdateConfigRequest $request): void
    {
        foreach (IPTVConfig::getAllBoleanSettings() as $config) {
            IPTVConfig::set(
                $config['name'],
                $request->boolean($config['name']),
                'bool',
            );
        }

        IPTVConfig::set(
            'CURRENT_LOCALE',
            $request->input('CURRENT_LOCALE'),
            'locale',
        );
    }
}
