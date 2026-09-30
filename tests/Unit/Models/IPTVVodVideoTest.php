<?php

namespace Tests\Unit\Models;

use App\Models\IPTVVodVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class IPTVVodVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_unique_slug_and_uuid_on_create(): void
    {
        $first = IPTVVodVideo::create(['name' => 'Movie Night']);
        $second = IPTVVodVideo::create(['name' => 'Movie Night']);

        $this->assertNotEmpty($first->uuid);
        $this->assertSame('movie-night', $first->slug);
        $this->assertSame('movie-night-2', $second->slug);
    }

    public function test_scope_where_identifier_resolves_id_slug_and_uuid(): void
    {
        $vod = IPTVVodVideo::create(['name' => 'Identifier Movie']);

        $this->assertSame(
            $vod->id,
            IPTVVodVideo::query()->whereIdentifier((string) $vod->id)->firstOrFail()->id
        );

        $this->assertSame(
            $vod->id,
            IPTVVodVideo::query()->whereIdentifier($vod->slug)->firstOrFail()->id
        );

        $this->assertSame(
            $vod->id,
            IPTVVodVideo::query()->whereIdentifier($vod->uuid)->firstOrFail()->id
        );
    }

    public function test_is_playable_attribute_depends_on_disk_and_path(): void
    {
        $vod = IPTVVodVideo::create(['name' => 'Playable Movie']);
        $this->assertFalse($vod->is_playable);

        $vod->update([
            'disk' => 'vod-master',
            'path' => "vod/{$vod->uuid}/movie.mp4",
        ]);

        $this->assertTrue($vod->fresh()->is_playable);
    }
}
