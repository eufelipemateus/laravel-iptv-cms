<?php

namespace App\Services\Vod;

use App\Models\IPTVVodVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;

class VodAssetService
{
    public function store(IPTVVodVideo $vod, UploadedFile $file): IPTVVodVideo
    {
        $disk = config('vod.disk', 'vod-master');
        $extension = strtolower($file->guessExtension() ?: $file->extension() ?: 'bin');
        $path = "vod/{$vod->uuid}/video-".Str::uuid().".{$extension}";
        $oldDisk = $vod->disk;
        $oldPath = $vod->path;

        try {
            $stored = Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));

            if (! $stored || ! Storage::disk($disk)->exists($path)) {
                throw new RuntimeException('Unable to store the VOD asset.');
            }

            $vod->forceFill([
                'disk' => $disk,
                'path' => $path,
                'original_filename' => $file->getClientOriginalName(),
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ])->save();
        } catch (Throwable $exception) {
            Storage::disk($disk)->delete($path);

            throw $exception;
        }

        if ($oldDisk && $oldPath) {
            Storage::disk($oldDisk)->delete($oldPath);
        }

        return $vod;
    }

    public function delete(IPTVVodVideo $vod): void
    {
        if ($vod->disk) {
            Storage::disk($vod->disk)->deleteDirectory("vod/{$vod->uuid}");
        }
    }

    public function responseFor(IPTVVodVideo $vod): BinaryFileResponse
    {
        abort_unless($vod->disk && $vod->path && Storage::disk($vod->disk)->exists($vod->path), 404);

        return response()->file(Storage::disk($vod->disk)->path($vod->path), [
            'Content-Type' => $vod->mime_type ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="'.addslashes($vod->original_filename ?: $vod->slug).'"',
        ]);
    }
}
