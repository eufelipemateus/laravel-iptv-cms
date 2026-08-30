<?php

namespace Tests\Unit\Services;

use App\Jobs\SyncEpgSource;
use App\Models\EpgSource;
use App\Services\Epg\EpgImportException;
use App\Services\Epg\EpgSyncService;
use App\Services\Epg\XmltvDownloader;
use App\Services\Epg\XmltvImporter;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
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
        config([
            'modules.epg.sync_lock_seconds' => 900,
            'modules.epg.queue_connection' => 'database',
            'modules.epg.queue' => 'epg',
        ]);
        $source = $this->source();
        $job = new SyncEpgSource($source);

        $this->assertInstanceOf(ShouldBeUnique::class, $job);
        $this->assertInstanceOf(ShouldQueue::class, $job);
        $this->assertSame((string) $source->id, $job->uniqueId());
        $this->assertSame(900, $job->uniqueFor());
        $this->assertSame('database', $job->connection);
        $this->assertSame('epg', $job->queue);
        $this->assertSame(1800, $job->timeout);
        $this->assertSame(3, $job->tries);
        $this->assertSame([60, 300, 900], $job->backoff());
    }

    public function test_database_queue_storage_is_available(): void
    {
        $this->assertTrue(Schema::hasTable('jobs'));
        $this->assertTrue(Schema::hasTable('failed_jobs'));
    }

    public function test_default_epg_queue_connection_is_asynchronous(): void
    {
        $this->assertSame('database', config('modules.epg.queue_connection'));
        $this->assertNotSame('sync', config('modules.epg.queue_connection'));
        $this->assertSame('epg', config('modules.epg.queue'));
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

    public function test_job_timeout_lock_and_retry_after_have_safe_configured_margins(): void
    {
        config(['modules.epg.job_timeout' => 1200, 'modules.epg.sync_lock_seconds' => 2400]);
        $job = new SyncEpgSource($this->source());
        $this->assertSame(1200, $job->timeout);
        $this->assertSame(2400, $job->uniqueFor());
        $this->assertGreaterThan($job->timeout, config('queue.connections.database.retry_after'));
        $this->assertGreaterThan(config('queue.connections.database.retry_after'), $job->uniqueFor());
    }

    public function test_due_sources_use_refresh_interval_after_success_and_short_retry_after_failure(): void
    {
        config(['modules.epg.error_retry_minutes' => 15]);
        $never = $this->source(['name' => 'Never']);
        $successDue = $this->source(['name' => 'Success due', 'last_sync_at' => now()->subMinutes(61), 'last_success_at' => now()->subMinutes(61)]);
        $successWaiting = $this->source(['name' => 'Success waiting', 'last_sync_at' => now()->subMinutes(59), 'last_success_at' => now()->subMinutes(59)]);
        $failureDue = $this->source(['name' => 'Failure due', 'last_sync_at' => now()->subMinutes(16), 'last_error_at' => now()->subMinutes(16)]);
        $failureWaiting = $this->source(['name' => 'Failure waiting', 'last_sync_at' => now()->subMinutes(14), 'last_error_at' => now()->subMinutes(14)]);
        $disabled = $this->source(['name' => 'Disabled', 'enabled' => false]);
        $dueIds = EpgSource::due()->modelKeys();
        $this->assertContains($never->id, $dueIds);
        $this->assertContains($successDue->id, $dueIds);
        $this->assertNotContains($successWaiting->id, $dueIds);
        $this->assertContains($failureDue->id, $dueIds);
        $this->assertNotContains($failureWaiting->id, $dueIds);
        $this->assertNotContains($disabled->id, $dueIds);
    }

    private function source(array $attributes = []): EpgSource
    {
        $source = EpgSource::create(array_merge([
            'name' => 'Guide', 'url' => 'https://example.com/guide.xml', 'enabled' => true,
            'format' => 'xmltv', 'timezone' => 'UTC', 'refresh_interval' => 60,
        ], $attributes));
        $source->forceFill($attributes)->save();

        return $source;
    }
}
