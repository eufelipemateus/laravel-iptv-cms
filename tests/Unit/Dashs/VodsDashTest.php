<?php

namespace Tests\Unit\Dashs;

use App\Dashs\Vods;
use App\Models\IPTVVodVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VodsDashTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_returns_expected_totals_and_storage(): void
    {
        IPTVVodVideo::create(['name' => 'Movie 1', 'file_size' => 10]);
        IPTVVodVideo::create(['name' => 'Movie 2', 'file_size' => 15]);

        $view = Vods::view();
        $data = $view->getData();

        $this->assertSame(2, $data['total']);
        $this->assertSame(25, $data['storage']);
    }
}
