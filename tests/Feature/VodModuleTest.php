<?php

namespace Tests\Feature;

use App\Models\IPTVVodVideo;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VodModuleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['modules.vod.enabled' => true]);
        Storage::fake('vod-master');
        $this->actingAs(User::factory()->create(['is_admin' => true]));
    }

    public function test_admin_can_create_update_replace_and_delete_a_vod_asset(): void
    {
        $this->post(route('vods.store'), [
            'name' => 'First movie',
            'description' => 'Original description',
            'file' => UploadedFile::fake()->create('first.mp4', 100, 'video/mp4'),
        ])->assertRedirect();

        $vod = IPTVVodVideo::firstOrFail();
        $oldPath = $vod->path;

        $this->assertNotEmpty($vod->uuid);
        $this->assertSame('first-movie', $vod->slug);
        $this->assertSame('first.mp4', $vod->original_filename);
        $this->assertSame(102400, $vod->file_size);
        Storage::disk('vod-master')->assertExists($oldPath);

        $this->post(route('vods.update', $vod), [
            'name' => 'Renamed movie',
            'description' => 'Updated description',
            'file' => UploadedFile::fake()->create('replacement.webm', 200, 'video/webm'),
        ])->assertRedirect();

        $vod->refresh();

        $this->assertSame('first-movie', $vod->slug);
        $this->assertSame('Updated description', $vod->description);
        $this->assertSame('replacement.webm', $vod->original_filename);
        Storage::disk('vod-master')->assertMissing($oldPath);
        Storage::disk('vod-master')->assertExists($vod->path);

        $path = $vod->path;
        $this->delete(route('vods.delete', $vod))->assertRedirect(route('vods.list'));

        $this->assertDatabaseMissing('iptv_vods', ['id' => $vod->id]);
        Storage::disk('vod-master')->assertMissing($path);
    }

    public function test_public_api_uses_canonical_routes_and_resolves_all_identifiers(): void
    {
        $vod = $this->createPlayableVod('API movie');
        IPTVVodVideo::create(['name' => 'Without video']);

        $this->get(route('api.vods.list', ['per_page' => 999]))
            ->assertOk()
            ->assertJsonPath('meta.per_page', 100)
            ->assertJsonPath('data.0.id', $vod->id)
            ->assertJsonMissing(['name' => 'Without video']);

        foreach ([$vod->id, $vod->slug, $vod->uuid] as $identifier) {
            $this->get(route('api.vods.show', ['id' => $identifier]))
                ->assertOk()
                ->assertJsonPath('data.id', $vod->id);
        }

        $this->get(route('api.vods.playback', ['id' => $vod->slug]))
            ->assertOk()
            ->assertHeader('Content-Type', 'video/mp4');

        $missingFile = IPTVVodVideo::create(['name' => 'Missing asset']);
        $missingFile->update([
            'disk' => 'vod-master',
            'path' => "vod/{$missingFile->uuid}/missing.mp4",
        ]);

        $this->get(route('api.vods.playback', ['id' => $missingFile->id]))->assertNotFound();
    }

    public function test_disabled_module_hides_the_admin_and_api_endpoints(): void
    {
        config(['modules.vod.enabled' => false]);

        $this->get(route('vods.list'))->assertNotFound();
        $this->get(route('api.vods.list'))->assertNotFound();
    }

    private function createPlayableVod(string $name): IPTVVodVideo
    {
        $vod = IPTVVodVideo::create(['name' => $name]);
        $path = "vod/{$vod->uuid}/video.mp4";
        Storage::disk('vod-master')->put($path, 'video');

        $vod->update([
            'disk' => 'vod-master',
            'path' => $path,
            'original_filename' => 'video.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 5,
        ]);

        return $vod;
    }
}
