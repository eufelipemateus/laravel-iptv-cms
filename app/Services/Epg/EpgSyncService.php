<?php

namespace App\Services\Epg;

use App\Models\EpgSource;
use Illuminate\Support\Facades\Cache;
use Throwable;

class EpgSyncService
{
    public function __construct(
        private XmltvDownloader $downloader,
        private XmltvImporter $importer,
    ) {}

    /** @return array{channels:int,programmes:int} */
    public function sync(EpgSource $source): array
    {
        if (! config('modules.epg.enabled', true)) {
            throw new EpgImportException('The EPG module is disabled.');
        }

        if (! $source->enabled) {
            throw new EpgImportException('The EPG source is disabled.');
        }

        $lock = Cache::lock('epg:sync:'.$source->id, (int) config('modules.epg.sync_lock_seconds', 1800));
        if (! $lock->get()) {
            throw new EpgImportException('This EPG source is already being synchronized.');
        }

        $path = null;
        $source->forceFill(['last_sync_at' => now(), 'last_error' => null, 'last_error_at' => null])->save();
        try {
            $path = $this->downloader->download($source->url);
            $result = $this->importer->import($source, $path);
            $source->forceFill(['last_success_at' => now(), 'last_error' => null, 'last_error_at' => null])->save();
            return $result;
        } catch (Throwable $exception) {
            $source->forceFill([
                'last_error_at' => now(),
                'last_error' => mb_substr($exception->getMessage(), 0, 65535),
            ])->save();
            throw $exception;
        } finally {
            if (is_string($path)) {
                @unlink($path);
            }
            $lock->release();
        }
    }
}
