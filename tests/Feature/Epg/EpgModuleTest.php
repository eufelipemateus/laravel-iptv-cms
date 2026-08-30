<?php

namespace Tests\Feature\Epg;

use App\Jobs\SyncEpgSource;
use App\Models\ChannelCdn;
use App\Models\Customer;
use App\Models\CustomerPlan;
use App\Models\EpgChannel;
use App\Models\EpgProgramme;
use App\Models\EpgSource;
use App\Models\User;
use App\Services\Epg\XmltvGenerator;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
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
        $response->assertSee('tvg-id="'.$epg->xmltvId().'"', false);
        $this->assertSame($epg->id, $channel->epgChannel->id);
    }

    public function test_global_xmltv_escapes_values_and_formats_programmes(): void
    {
        $startAt = now()->addHour()->startOfMinute();
        $endAt = $startAt->copy()->addHour();
        $epg = $this->epgChannel('news.br', 'News & More');
        $cdn = ChannelCdn::factory()->create();
        $this->makePlayableChannel($cdn, null, ['epg_channel_id' => $epg->id]);
        EpgProgramme::create([
            'epg_channel_id' => $epg->id,
            'external_id' => 'show-1',
            'title' => 'News <Live>',
            'description' => 'A & B',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'sync_generation' => $epg->source->active_sync_generation,
        ]);

        $response = $this->get(route('epg.public'));
        $content = $response->streamedContent();

        $response->assertOk()->assertHeader('Content-Type', 'application/xml; charset=utf-8');
        $this->assertStringContainsString('<channel id="'.$epg->xmltvId().'">', $content);
        $this->assertStringContainsString('News &amp; More', $content);
        $this->assertStringContainsString('News &lt;Live&gt;', $content);
        $this->assertStringContainsString('start="'.$startAt->format('YmdHis O').'"', $content);
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

        $this->assertStringContainsString('id="'.$allowed->xmltvId().'"', $content);
        $this->assertStringNotContainsString('id="'.$denied->xmltvId().'"', $content);
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

    public function test_duplicate_external_ids_are_unique_and_consistent_across_xmltv_and_m3u(): void
    {
        $this->enablePublicCdn();
        $cdn = ChannelCdn::factory()->create(['slug' => 'multi-source']);
        $first = $this->epgChannel('news', 'First News');
        $second = $this->epgChannel('news', 'Second News');
        $this->makePlayableChannel($cdn, null, ['name' => 'First', 'number' => 201, 'epg_channel_id' => $first->id]);
        $this->makePlayableChannel($cdn, null, ['name' => 'Second', 'number' => 202, 'epg_channel_id' => $second->id]);

        foreach ([$first, $second] as $index => $epg) {
            EpgProgramme::create([
                'epg_channel_id' => $epg->id,
                'external_id' => 'show-'.$index,
                'title' => 'Programme '.$index,
                'start_at' => now()->addHour(),
                'end_at' => now()->addHours(2),
                'sync_generation' => $epg->source->active_sync_generation,
            ]);
        }

        $xml = $this->get(route('epg.public'))->streamedContent();
        $playlist = $this->get(route('cdn-playslit', $cdn->slug))->getContent();

        $this->assertNotSame($first->xmltvId(), $second->xmltvId());
        foreach ([$first, $second] as $epg) {
            $this->assertStringContainsString('<channel id="'.$epg->xmltvId().'">', $xml);
            $this->assertStringContainsString('channel="'.$epg->xmltvId().'"', $xml);
            $this->assertStringContainsString('tvg-id="'.$epg->xmltvId().'"', $playlist);
        }
    }

    public function test_disabled_source_is_removed_from_xmltv_and_m3u_then_restored_without_remapping(): void
    {
        $this->enablePublicCdn();
        $cdn = ChannelCdn::factory()->create(['slug' => 'toggle-source']);
        $epg = $this->epgChannel('toggle', 'Toggle Channel');
        $channel = $this->makePlayableChannel($cdn, null, ['epg_channel_id' => $epg->id]);
        EpgProgramme::create([
            'epg_channel_id' => $epg->id, 'external_id' => 'toggle-show', 'title' => 'Toggle Show',
            'start_at' => now(), 'end_at' => now()->addHour(),
            'sync_generation' => $epg->source->active_sync_generation,
        ]);

        $this->assertStringContainsString($epg->xmltvId(), $this->get(route('epg.public'))->streamedContent());
        $this->assertStringContainsString('tvg-id="'.$epg->xmltvId().'"', $this->get(route('cdn-playslit', $cdn->slug))->getContent());
        $epg->source->update(['enabled' => false]);
        $xml = $this->get(route('epg.public'))->streamedContent();
        $playlist = $this->get(route('cdn-playslit', $cdn->slug))->getContent();
        $this->assertStringNotContainsString($epg->xmltvId(), $xml);
        $this->assertStringNotContainsString('Toggle Show', $xml);
        $this->assertStringNotContainsString('tvg-id="'.$epg->xmltvId().'"', $playlist);
        $this->assertSame($epg->id, $channel->fresh()->epg_channel_id);
        $epg->source->update(['enabled' => true]);
        $this->assertStringContainsString($epg->xmltvId(), $this->get(route('epg.public'))->streamedContent());
        $this->assertStringContainsString('tvg-id="'.$epg->xmltvId().'"', $this->get(route('cdn-playslit', $cdn->slug))->getContent());
    }

    public function test_public_xmltv_supports_conditional_http_cache_without_running_generator(): void
    {
        config(['modules.epg.http_cache_seconds' => 300]);
        $response = $this->get(route('epg.public'));
        $etag = $response->headers->get('ETag');
        $response->assertOk()->assertHeader('Cache-Control', 'max-age=300, public');
        $this->assertNotNull($etag);

        $generator = Mockery::mock(XmltvGenerator::class);
        $generator->shouldNotReceive('stream');
        $this->app->instance(XmltvGenerator::class, $generator);
        $this->withHeader('If-None-Match', $etag)->get(route('epg.public'))->assertStatus(304);
    }

    public function test_public_xmltv_has_a_specific_rate_limit(): void
    {
        config(['modules.epg.rate_limit_per_minute' => 2]);
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.44'])->get(route('epg.public'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.44'])->get(route('epg.public'))->assertOk();
        $this->withServerVariables(['REMOTE_ADDR' => '192.0.2.44'])->get(route('epg.public'))->assertTooManyRequests();
    }

    public function test_admin_sync_action_queues_the_source(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $source = EpgSource::create([
            'name' => 'Queued guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ]);

        $this->actingAs($admin)
            ->from(route('epg.sources.index'))
            ->post(route('epg.sources.sync', $source))
            ->assertRedirect(route('epg.sources.index'))
            ->assertSessionHas('success', 'EPG synchronization queued.');

        Queue::assertPushed(SyncEpgSource::class, fn (SyncEpgSource $job) => $job->source->is($source));
    }

    public function test_admin_sync_action_rejects_a_disabled_source_without_queueing(): void
    {
        Queue::fake();
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $source = EpgSource::create([
            'name' => 'Disabled guide', 'url' => 'https://example.com/guide.xml', 'enabled' => false,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ]);

        $this->actingAs($admin)
            ->post(route('epg.sources.sync', $source))
            ->assertSessionHasErrors(['sync' => 'The EPG source is disabled.']);

        Queue::assertNothingPushed();
    }

    public function test_admin_sync_action_reports_queue_infrastructure_failure(): void
    {
        $admin = User::factory()->create(['is_admin' => true, 'active' => true]);
        $source = EpgSource::create([
            'name' => 'Unavailable queue', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ]);
        $dispatcher = Mockery::mock(Dispatcher::class);
        $dispatcher->shouldReceive('dispatch')->once()->andThrow(new \RuntimeException('Database unavailable'));
        $this->app->instance(Dispatcher::class, $dispatcher);

        $this->actingAs($admin)
            ->post(route('epg.sources.sync', $source))
            ->assertSessionHasErrors(['sync' => 'Unable to queue EPG synchronization.']);
    }

    private function epgChannel(string $externalId, string $name): EpgChannel
    {
        $source = EpgSource::create([
            'name' => 'Guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
            'active_sync_generation' => '00000000-0000-4000-8000-000000000001',
        ]);

        return EpgChannel::create(['epg_source_id' => $source->id, 'external_id' => $externalId, 'display_name' => $name, 'name' => $name]);
    }
}
