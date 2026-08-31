<?php

namespace Tests\Feature\OperationMode;

use App\Enums\OperationMode;
use App\Models\Channel;
use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Tests\Concerns\BuildsIptvFixtures;
use Tests\TestCase;

class OperationModeFeatureTest extends TestCase
{
    use BuildsIptvFixtures;
    use RefreshDatabase;

    /**
     * @return array{0: string, 1: string}
     */
    private function tokenCredentialsFor(Customer $customer): array
    {
        $token = $customer->issueAuthToken();
        $parts = explode('.', $token, 2);

        return [$parts[0], $parts[1]];
    }

    private function createPlaylistFixtures(): array
    {
        $cdn = ChannelCdn::factory()->create(['slug' => 'mode-cdn']);
        $plan = CustomerPlan::factory()->active()->create();
        $customer = Customer::factory()->active()->create([
            'iptv_plan_id' => $plan->id,
            'iptv_cdn_id' => $cdn->id,
        ]);

        $this->makePlayableChannel($cdn, $plan, [
            'number' => 10,
            'name' => 'Mode Channel',
        ], [
            'url_stream' => 'https://cdn.test/mode.m3u8',
        ]);

        return [$cdn, $customer, $plan];
    }

    public function testM3u8ModeKeepsPlaylistRoutesActiveAndBlocksTv3Prefixes(): void
    {
        $this->setOperationMode(OperationMode::M3U8);
        $this->enablePublicCdn();
        [$cdn, $customer] = $this->createPlaylistFixtures();

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))
            ->assertOk()
            ->assertSee('#EXTM3U', false);

        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);
        $this->withBasicAuth($tokenId, $tokenSecret)
            ->get(route('client-playlist', ['slug' => $cdn->slug]))
            ->assertOk()
            ->assertSee('#EXTM3U', false);

        $this->get('/api/v1/tv/catalog')->assertNotFound();
        $this->get('/tv3/app')->assertNotFound();
        $this->get(route('dashboard'))->assertDontSee('/tv3/', false);
    }

    public function testDtv3ModeBlocksM3u8RoutesAndHidesM3u8Buttons(): void
    {
        $this->setOperationMode(OperationMode::DTV3);
        $this->enablePublicCdn();
        $this->enablePlaylistDownload();
        [$cdn, $customer] = $this->createPlaylistFixtures();

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))->assertNotFound();

        [$tokenId, $tokenSecret] = $this->tokenCredentialsFor($customer);
        $this->withBasicAuth($tokenId, $tokenSecret)
            ->get(route('client-playlist', ['slug' => $cdn->slug]))
            ->assertNotFound();

        $this->get(route('list_channel_cdn'))
            ->assertOk()
            ->assertDontSee('Playslit')
            ->assertDontSee('Donwload');

        $this->get(route('show_customer', ['id' => $customer->id]))
            ->assertOk()
            ->assertDontSee(__('Personal URL'));
    }

    public function testDtv3ModeAllowsDtv3RoutesWhenDeclaredWithModeMiddleware(): void
    {
        $this->setOperationMode(OperationMode::DTV3);

        Route::middleware('operation-mode:dtv3')->get('/api/v1/tv/bootstrap', fn () => response()->json([
            'bootstrap' => true,
        ]));
        Route::middleware('operation-mode:dtv3')->get('/tv3/client-app', fn () => response('ok'));

        $this->getJson('/api/v1/tv/bootstrap')->assertOk()->assertJson(['bootstrap' => true]);
        $this->get('/tv3/client-app')->assertOk()->assertSee('ok');
    }

    public function testModeChangeRequiresConfirmationAndRejectsInvalidValues(): void
    {
        $this->setOperationMode(OperationMode::M3U8);

        $this->from(route('config'))
            ->post(route('config_save'), [
                'CURRENT_LOCALE' => 'en',
                'mode' => OperationMode::DTV3->value,
                'confirm_mode_change' => '0',
            ])
            ->assertRedirect(route('config'))
            ->assertSessionHasErrors(['mode']);

        $this->from(route('config'))
            ->post(route('config_save'), [
                'CURRENT_LOCALE' => 'en',
                'mode' => 'invalid-mode',
            ])
            ->assertRedirect(route('config'))
            ->assertSessionHasErrors(['mode']);
    }

    public function testSwitchingModePreservesDataInvalidatesCacheAndAppliesImmediately(): void
    {
        $this->setOperationMode(OperationMode::M3U8);
        $this->enablePublicCdn();
        [$cdn] = $this->createPlaylistFixtures();

        Cache::put('operation-mode-switch-marker', 'present', 600);

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))->assertOk();

        $this->post(route('config_save'), [
            'CURRENT_LOCALE' => 'en',
            'mode' => OperationMode::DTV3->value,
            'confirm_mode_change' => '1',
            'URL_CDN' => '1',
        ])->assertRedirect(route('config'));

        $this->assertFalse(Cache::has('operation-mode-switch-marker'));
        $this->assertDatabaseHas('iptv_channels', ['name' => 'Mode Channel']);
        $this->assertDatabaseCount('iptv_channels', 1);

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))->assertNotFound();

        Route::middleware('operation-mode:dtv3')->get('/api/v1/tv/heartbeat', fn () => response()->json([
            'ok' => true,
        ]));

        $this->getJson('/api/v1/tv/heartbeat')->assertOk()->assertJson(['ok' => true]);

        $this->post(route('config_save'), [
            'CURRENT_LOCALE' => 'en',
            'mode' => OperationMode::M3U8->value,
            'confirm_mode_change' => '1',
            'URL_CDN' => '1',
        ])->assertRedirect(route('config'));

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))->assertOk();

        $this->post(route('config_save'), [
            'CURRENT_LOCALE' => 'en',
            'mode' => OperationMode::DTV3->value,
            'confirm_mode_change' => '1',
            'URL_CDN' => '1',
        ])->assertRedirect(route('config'));

        $this->assertNotSame(OperationMode::M3U8->value, OperationMode::DTV3->value);
    }

    public function testLegacyInstallationDefaultsToM3u8Mode(): void
    {
        $this->assertSame('m3u8', (string) app(\App\Services\OperationModeService::class)->current()->value);
        $this->assertFalse(\App\Models\IPTVConfig::has('mode'));
    }

    public function testGlobalModeGuardReturns404ForM3u8PrefixesInDtv3Mode(): void
    {
        $this->setOperationMode(OperationMode::DTV3);

        $this->get('/public/m3u8/legacy')->assertNotFound();
        $this->get('/client/m3u8/legacy')->assertNotFound();
    }
}
