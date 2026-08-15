<?php

namespace Tests\Feature\Epg;

use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuildsIptvFixtures;
use Tests\TestCase;

class EpgModuleTest extends TestCase
{
    use BuildsIptvFixtures, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['modules.epg.enabled' => true]);
    }

    public function test_mapped_channel_adds_xmltv_metadata_to_public_playlist(): void
    {
        $this->enablePublicCdn();
        $cdn = ChannelCdn::factory()->create(['slug' => 'epg-cdn']);
        $epg = $this->epgChannel('news.br', 'News & More');
        $channel = $this->makePlayableChannel($cdn, null, ['name' => 'News', 'epg_channel_id' => $epg->id]);

        $response = $this->get(route('cdn-playslit', $cdn->slug));

        $response->assertOk();
        $response->assertSee('url-tvg="'.route('epg.public').'"', false);
        $response->assertSee('tvg-id="news.br"', false);
        $this->assertSame($epg->id, $channel->epgChannel->id);
    }

    public function test_global_xmltv_escapes_values_and_formats_programmes(): void
    {
        $epg = $this->epgChannel('news.br', 'News & More');
        $cdn = ChannelCdn::factory()->create();
        $this->makePlayableChannel($cdn, null, ['epg_channel_id' => $epg->id]);
        EpgProgramme::create([
            'epg_channel_id' => $epg->id,
            'external_id' => 'show-1',
            'title' => 'News <Live>',
            'description' => 'A & B',
            'start_at' => '2026-08-14 18:00:00',
            'end_at' => '2026-08-14 19:00:00',
        ]);

        $response = $this->get(route('epg.public'));
        $content = $response->streamedContent();

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString('<channel id="news.br">', $content);
        $this->assertStringContainsString('News &amp; More', $content);
        $this->assertStringContainsString('News &lt;Live&gt;', $content);
        $this->assertStringContainsString('start="20260814180000 +0000"', $content);
    }

    public function test_private_xmltv_requires_auth_and_only_contains_customer_channels(): void
    {
        $cdn = ChannelCdn::factory()->create(['slug' => 'private-epg']);
        $plan = CustomerPlan::factory()->active()->create();
        $otherPlan = CustomerPlan::factory()->active()->create();
        $customer = Customer::factory()->active()->create(['iptv_plan_id' => $plan->id, 'iptv_cdn_id' => $cdn->id]);
        $allowed = $this->epgChannel('allowed', 'Allowed');
        $denied = $this->epgChannel('denied', 'Denied');
        $this->makePlayableChannel($cdn, $plan, ['epg_channel_id' => $allowed->id]);
        $this->makePlayableChannel($cdn, $otherPlan, ['epg_channel_id' => $denied->id]);

        $url = route('epg.customer', $cdn->slug);
        $this->get($url)->assertUnauthorized();
        [$id, $secret] = explode('.', $customer->issueAuthToken(), 2);
        $content = $this->withBasicAuth($id, $secret)->get($url)->streamedContent();

        $this->assertStringContainsString('id="allowed"', $content);
        $this->assertStringNotContainsString('id="denied"', $content);
    }

    public function test_disabled_module_returns_404_and_keeps_playlist_without_epg_metadata(): void
    {
        config(['modules.epg.enabled' => false]);
        $this->get(route('epg.public'))->assertNotFound();
        $this->enablePublicCdn();
        $cdn = ChannelCdn::factory()->create();
        $this->makePlayableChannel($cdn);
        $response = $this->get(route('cdn-playslit', $cdn->slug));
        $response->assertOk()->assertDontSee('url-tvg=', false);
    }

    private function epgChannel(string $externalId, string $name): EpgChannel
    {
        $source = EpgSource::create([
            'name' => 'Guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ]);

        return EpgChannel::create(['epg_source_id' => $source->id, 'external_id' => $externalId, 'display_name' => $name, 'name' => $name]);
    }
}
