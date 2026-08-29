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
        return $query->where('enabled', true)->where(function ($query): void {
            $query->whereNull('last_sync_at')->orWhereRaw(
                'last_sync_at <= ?',
                [now()->subMinutes(1)->toDateTimeString()],
            );
        })->get()->filter(fn (self $source): bool => $source->last_sync_at === null
            || $source->last_sync_at->addMinutes($source->refresh_interval)->isPast()
        );
    }
}
