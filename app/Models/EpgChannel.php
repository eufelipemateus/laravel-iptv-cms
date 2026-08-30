<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EpgChannel extends Model
{
    use HasFactory;

    protected $fillable = ['epg_source_id', 'external_id', 'name', 'display_name', 'icon_url', 'language', 'country', 'metadata', 'is_active', 'pending_sync_generation'];

    protected $casts = ['metadata' => 'array', 'is_active' => 'boolean'];

    public function xmltvId(): string
    {
        return self::makeXmltvId($this->epg_source_id, $this->external_id);
    }

    public static function makeXmltvId(int|string $sourceId, string $externalId): string
    {
        return $sourceId.':'.$externalId;
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(EpgSource::class, 'epg_source_id');
    }

    public function programmes(): HasMany
    {
        return $this->hasMany(EpgProgramme::class);
    }

    public function channels(): HasMany
    {
        return $this->hasMany(Channel::class);
    }
}
