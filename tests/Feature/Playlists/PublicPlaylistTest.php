<?php

namespace Tests\Feature\Playlists;

use App\Models\ChannelCdn;
use App\Models\IPTVVodVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuildsIptvFixtures;
use Tests\TestCase;

class PublicPlaylistTest extends TestCase
{
    use BuildsIptvFixtures;
    use RefreshDatabase;

    public function test_public_playlist_contains_only_requested_cdn_in_m3u_order_with_download_headers(): void
    {
        $this->enablePublicCdn();
        $this->enablePlaylistDownload();
        $cdn = ChannelCdn::factory()->create(['slug' => 'main-cdn']);
        $otherCdn = ChannelCdn::factory()->create(['slug' => 'other-cdn']);

        $first = $this->makePlayableChannel($cdn, null, [
            'number' => 1,
            'name' => 'Alpha',
            'logo' => 'logos/alpha.png',
        ], ['url_stream' => 'https://cdn.test/alpha.m3u8']);
        $second = $this->makePlayableChannel($cdn, null, [
            'number' => 2,
            'name' => 'Beta',
            'logo' => 'logos/beta.png',
        ], ['url_stream' => 'https://cdn.test/beta.m3u8']);
        $this->makePlayableChannel($otherCdn, null, ['name' => 'Other']);

        $response = $this->get(route('cdn-playslit', ['slug' => 'main-cdn']));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/plain; charset=utf-8');
        $response->assertHeader('Content-Disposition', 'attachment; filename=main-cdn.m3u8');
        $lines = $this->playlistLines($response->getContent());

        $this->assertSame('#EXTM3U', $lines[0]);
        $this->assertStringContainsString($first->name, $lines[1]);
        $this->assertSame('https://cdn.test/alpha.m3u8', $lines[2]);
        $this->assertStringContainsString($second->name, $lines[3]);
        $this->assertSame('https://cdn.test/beta.m3u8', $lines[4]);
        $this->assertStringNotContainsString('Other', $response->getContent());
    }

    public function test_playlist_escapes_line_breaks_so_database_values_cannot_inject_entries(): void
    {
        $this->enablePublicCdn();
        $cdn = ChannelCdn::factory()->create(['slug' => 'safe-cdn']);
        $this->makePlayableChannel($cdn, null, [
            'number' => 9,
            'name' => "Injected\n#EXTINF:-1,Owned",
        ], [
            'url_stream' => "https://cdn.test/live.m3u8\r\n#EXTINF:-1,Owned",
        ]);

        $response = $this->get(route('cdn-playslit', ['slug' => 'safe-cdn']));
        $lines = $this->playlistLines($response->getContent());
        $extinfLines = array_values(array_filter($lines, fn (string $line) => str_starts_with($line, '#EXTINF')));

        $response->assertOk();
        $this->assertCount(1, $extinfLines);
        $this->assertSame(3, count($lines));
    }

    public function test_public_playlist_includes_playable_vods_only_when_enabled(): void
    {
        $this->enablePublicCdn();
        config(['modules.vod.enabled' => true]);
        Storage::fake('vod-master');
        $cdn = ChannelCdn::factory()->create(['slug' => 'vod-cdn']);
        $this->makePlayableChannel($cdn, null, ['name' => 'Live channel']);
        $vod = IPTVVodVideo::create(['name' => 'Movie night']);
        $path = "vod/{$vod->uuid}/movie.mp4";
        Storage::disk('vod-master')->put($path, 'video');
        $vod->update(['disk' => 'vod-master', 'path' => $path]);

        $response = $this->get(route('cdn-playslit', ['slug' => $cdn->slug]));

        $response->assertOk();
        $response->assertSee('Live channel', false);
        $response->assertSee('group-title="VOD",Movie night', false);
        $response->assertSee(route('api.v1.vods.playback', ['id' => $vod->slug]), false);

        config(['modules.vod.enabled' => false]);

        $this->get(route('cdn-playslit', ['slug' => $cdn->slug]))
            ->assertOk()
            ->assertDontSee('Movie night', false);
    }
}
