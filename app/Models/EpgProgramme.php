<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EpgProgramme extends Model
{
    use HasFactory;

    protected $fillable = [
        'epg_channel_id', 'external_id', 'title', 'subtitle', 'description', 'category',
        'icon_url', 'language', 'start_at', 'end_at', 'metadata',
    ];

    protected $casts = ['start_at' => 'datetime', 'end_at' => 'datetime', 'metadata' => 'array'];

    public function channel(): BelongsTo
    {
        return $this->belongsTo(EpgChannel::class, 'epg_channel_id');
    }
}
