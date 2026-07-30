<?php

namespace App\Actions\Config;

use App\Enums\OperationMode;
use App\Http\Requests\UpdateConfigRequest;
use App\Models\IPTVConfig;
use App\Services\OperationModeService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateConfigAction
{
    use AsAction;

    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    public function handle(UpdateConfigRequest $request): void
    {
        $previousMode = $this->operationModeService->current();
        $newMode = OperationMode::from((string) $request->input('mode'));

        if ($previousMode !== $newMode && ! $request->boolean('confirm_mode_change')) {
            throw ValidationException::withMessages([
                'mode' => __('MODE_CHANGE_CONFIRMATION_REQUIRED'),
            ]);
        }

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

        $this->operationModeService->set($newMode);

        if ($previousMode !== $newMode) {
            Cache::flush();

            Log::info('Operation mode changed.', [
                'from' => $previousMode->value,
                'to' => $newMode->value,
                'admin_user_id' => optional($request->user())->id,
                'admin_user_email' => optional($request->user())->email,
                'ip' => $request->ip(),
            ]);
        }
    }
}
