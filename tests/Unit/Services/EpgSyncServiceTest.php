<?php

namespace Tests\Unit\Services;

use App\Jobs\SyncEpgSource;
use App\Models\EpgSource;
use App\Services\Epg\EpgImportException;
use App\Services\Epg\EpgSyncService;
use App\Services\Epg\XmltvDownloader;
use App\Services\Epg\XmltvImporter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Tests\TestCase;

class EpgSyncServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['modules.epg.enabled' => true]);
    }

    public function test_job_is_unique_per_source_for_the_configured_duration(): void
    {
        config(['modules.epg.sync_lock_seconds' => 900]);
        $source = $this->source();
        $job = new SyncEpgSource($source);

        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertSame((string) $source->id, $job->uniqueId());
        $this->assertSame(900, $job->uniqueFor());
    }

    public function test_success_and_failure_update_sync_metadata(): void
    {
        $source = $this->source();
        $path = tempnam(sys_get_temp_dir(), 'sync-test-');
        $downloader = Mockery::mock(XmltvDownloader::class);
        $downloader->shouldReceive('download')->once()->andReturn($path);
        $importer = Mockery::mock(XmltvImporter::class);
        $importer->shouldReceive('import')->once()->andReturn(['channels' => 1, 'programmes' => 2]);

        $result = (new EpgSyncService($downloader, $importer))->sync($source);

        $this->assertSame(['channels' => 1, 'programmes' => 2], $result);
        $this->assertNotNull($source->fresh()->last_sync_at);
        $this->assertNotNull($source->fresh()->last_success_at);
        $this->assertNull($source->fresh()->last_error);

        $downloader = Mockery::mock(XmltvDownloader::class);
        $downloader->shouldReceive('download')->once()->andThrow(new EpgImportException('broken'));
        try {
            (new EpgSyncService($downloader, Mockery::mock(XmltvImporter::class)))->sync($source);
            $this->fail('The synchronization should fail.');
        } catch (EpgImportException) {
            $this->assertSame('broken', $source->fresh()->last_error);
            $this->assertNotNull($source->fresh()->last_error_at);
        }
    }

    public function test_disabled_module_source_and_concurrent_lock_are_rejected(): void
    {
        $service = new EpgSyncService(Mockery::mock(XmltvDownloader::class), Mockery::mock(XmltvImporter::class));
        config(['modules.epg.enabled' => false]);
        $this->expectException(EpgImportException::class);
        $service->sync($this->source());
    }

    public function test_existing_lock_rejects_concurrent_sync(): void
    {
        config(['cache.default' => 'array']);
        $source = $this->source();
        $lock = Cache::lock('epg:sync:'.$source->id, 1800);
        $this->assertTrue($lock->get());
        try {
            $this->expectException(EpgImportException::class);
            (new EpgSyncService(Mockery::mock(XmltvDownloader::class), Mockery::mock(XmltvImporter::class)))->sync($source);
        } finally {
            $lock->release();
        }
    }

    private function source(array $attributes = []): EpgSource
    {
        return EpgSource::create(array_merge([
            'name' => 'Guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ], $attributes));
    }
}
