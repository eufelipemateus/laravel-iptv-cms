<?php

namespace App\Services;

use App\Enums\OperationMode;
use App\Models\IPTVConfig;
use Illuminate\Support\Facades\Cache;

class OperationModeService
{
    public const CONFIG_KEY = 'mode';

    private const CACHE_KEY = 'operation_mode.current';

    public function current(): OperationMode
    {
        $mode = Cache::rememberForever(self::CACHE_KEY, function (): string {
            $configuredMode = (string) IPTVConfig::get(self::CONFIG_KEY, OperationMode::M3U8->value);

            return OperationMode::tryFrom($configuredMode)?->value ?? OperationMode::M3U8->value;
        });

        return OperationMode::from($mode);
    }

    public function is(OperationMode $mode): bool
    {
        return $this->current() === $mode;
    }

    public function isM3u8(): bool
    {
        return $this->is(OperationMode::M3U8);
    }

    public function isDtv3(): bool
    {
        return $this->is(OperationMode::DTV3);
    }

    public function set(OperationMode $mode): void
    {
        IPTVConfig::set(self::CONFIG_KEY, $mode->value, 'string');
        $this->forgetCachedMode();
    }

    public function forgetCachedMode(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
