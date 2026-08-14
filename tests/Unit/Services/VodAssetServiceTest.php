<?php

namespace Tests\Unit\Services;

use App\Models\IPTVVodVideo;
use App\Services\Vod\VodAssetService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class VodAssetServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_saves_metadata_and_replaces_old_asset(): void
    {
        config(['vod.disk' => 'vod-master']);
        Storage::fake('vod-master');

        $vod = IPTVVodVideo::create(['name' => 'Asset Movie']);
        $oldPath = "vod/{$vod->uuid}/old.mp4";
        Storage::disk('vod-master')->put($oldPath, 'old-video');
        $vod->update([
            'disk' => 'vod-master',
            'path' => $oldPath,
            'original_filename' => 'old.mp4',
            'mime_type' => 'video/mp4',
            'file_size' => 9,
        ]);

        $service = new VodAssetService;
        $file = UploadedFile::fake()->create('new.mp4', 50, 'video/mp4');

        $updated = $service->store($vod->fresh(), $file);

        $this->assertSame('vod-master', $updated->disk);
        $this->assertNotSame($oldPath, $updated->path);
        $this->assertSame('new.mp4', $updated->original_filename);
        Storage::disk('vod-master')->assertMissing($oldPath);
        Storage::disk('vod-master')->assertExists((string) $updated->path);
    }

    public function test_store_rolls_back_when_storage_fails(): void
    {
        config(['vod.disk' => 'vod-master']);

        $vod = IPTVVodVideo::create(['name' => 'Broken Asset Movie']);

        Storage::shouldReceive('disk')->with('vod-master')->twice()->andReturnSelf();
        Storage::shouldReceive('putFileAs')->once()->andReturn(false);
        Storage::shouldReceive('delete')->once()->andReturn(true);

        $service = new VodAssetService;
        $file = UploadedFile::fake()->create('broken.mp4', 10, 'video/mp4');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Unable to store the VOD asset.');

        try {
            $service->store($vod, $file);
        } finally {
            $this->assertNull($vod->fresh()->disk);
            $this->assertNull($vod->fresh()->path);
        }
    }
}
