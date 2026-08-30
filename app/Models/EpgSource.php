<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class EpgSource extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'url', 'enabled', 'format', 'timezone', 'refresh_interval', 'active_sync_generation'];

    protected $casts = [
        'enabled' => 'boolean',
        'last_sync_at' => 'datetime',
        'last_success_at' => 'datetime',
        'last_error_at' => 'datetime',
    ];

    public function channels(): HasMany
    {
        return $this->hasMany(EpgChannel::class);
    }

    public function programmes(): HasManyThrough
    {
        return $this->hasManyThrough(EpgProgramme::class, EpgChannel::class);
    }

    public function scopeDue($query)
    {
        return $query->where('enabled', true)->get()->filter(function (self $source): bool {
            if ($source->last_sync_at === null) {
                return true;
            }

            $failed = $source->last_error_at !== null
                && ($source->last_success_at === null || $source->last_error_at->greaterThan($source->last_success_at));
            $minutes = $failed
                ? (int) config('modules.epg.error_retry_minutes', 15)
                : $source->refresh_interval;

            return $source->last_sync_at->addMinutes($minutes)->isPast();
        });
    }
}
