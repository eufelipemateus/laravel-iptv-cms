<?php

namespace App\Services\Vod;

use App\Models\IPTVVodVideo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VodAssetService
{
    public function store(IPTVVodVideo $vod, UploadedFile $file): IPTVVodVideo
    {
        $disk = config('vod.disk', 'vod-master');
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension() ?: 'bin');
        $path = "vod/{$vod->uuid}/video.{$extension}";

        if ($vod->disk && $vod->path) {
            Storage::disk($vod->disk)->delete($vod->path);
        }

        Storage::disk($disk)->putFileAs(dirname($path), $file, basename($path));

        $vod->forceFill([
            'disk' => $disk,
            'path' => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'file_size' => $file->getSize(),
        ])->save();

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
