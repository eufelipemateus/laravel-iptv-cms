<?php

namespace Tests\Unit\Services;

use App\Enums\OperationMode;
use App\Models\IPTVConfig;
use App\Services\OperationModeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperationModeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testUsesM3u8AsDefaultModeForLegacyInstallations(): void
    {
        /** @var OperationModeService $service */
        $service = app(OperationModeService::class);

        $this->assertSame(OperationMode::M3U8, $service->current());
        $this->assertTrue($service->isM3u8());
        $this->assertFalse($service->isDtv3());
    }

    public function testSetUpdatesModeAndCacheIsRefreshed(): void
    {
        /** @var OperationModeService $service */
        $service = app(OperationModeService::class);

        $this->assertSame(OperationMode::M3U8, $service->current());

        $service->set(OperationMode::DTV3);
        $this->assertSame(OperationMode::DTV3, $service->current());

        IPTVConfig::set('mode', OperationMode::M3U8->value, 'string');
        $this->assertSame(OperationMode::DTV3, $service->current());

        $service->forgetCachedMode();
        $this->assertSame(OperationMode::M3U8, $service->current());
    }
}
