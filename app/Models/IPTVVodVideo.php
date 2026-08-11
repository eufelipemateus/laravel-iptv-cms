<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class IPTVVodVideo extends Model
{
    use HasFactory;

    protected $table = 'iptv_vods';

    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'disk',
        'path',
        'original_filename',
        'mime_type',
        'file_size',
    ];

    protected static function booted(): void
    {
        static::creating(function (IPTVVodVideo $vod) {
            $vod->uuid = $vod->uuid ?: (string) Str::uuid();
            $vod->slug = $vod->slug ?: static::makeUniqueSlug($vod->name);
        });
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        if (! $search) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    public function scopeWithVideo(Builder $query): Builder
    {
        return $query->whereNotNull('disk')->whereNotNull('path');
    }

    public function getIsPlayableAttribute(): bool
    {
        return (bool) ($this->disk && $this->path);
    }

    public static function makeUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: Str::uuid()->toString();
        $slug = $base;
        $count = 2;

        while (static::query()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->exists()
        ) {
            $slug = "{$base}-{$count}";
            $count++;
        }

        return $slug;
    }
}
