<?php

namespace App\Models;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AuditLog extends Model
{
    public const EVENTS = ['created', 'updated', 'deleted', 'restored'];

    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'restored_at' => 'datetime',
    ];

    public function canBeRestored(): bool
    {
        if (! in_array($this->event, ['created', 'updated', 'deleted'], true)
            || $this->restored_at !== null
            || ! in_array($this->auditable_type, config('audit.restorable_models', []), true)
            || ! class_exists($this->auditable_type)
            || ! is_subclass_of($this->auditable_type, Model::class)) {
            return false;
        }

        if ($this->event === 'created') {
            return is_array($this->new_values) && $this->new_values !== [];
        }

        if ($this->event === 'updated') {
            return is_array($this->old_values)
                && $this->old_values !== []
                && is_array($this->new_values)
                && $this->new_values !== []
                && array_diff_key($this->old_values, $this->new_values) === []
                && array_diff_key($this->new_values, $this->old_values) === [];
        }

        if (! is_array($this->old_values) || $this->old_values === []) {
            return false;
        }

        /** @var class-string<Model> $modelClass */
        $modelClass = $this->auditable_type;
        $keyName = (new $modelClass)->getKeyName();

        return array_key_exists($keyName, $this->old_values)
            && (string) $this->old_values[$keyName] === (string) $this->auditable_id;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'restored_from_id');
    }

    public function restoration(): HasOne
    {
        return $this->hasOne(self::class, 'restored_from_id');
    }

    public function scopeForEvent(Builder $query, ?string $event): Builder
    {
        return $event === null ? $query : $query->where('event', $event);
    }

    public function scopeForAuditableType(Builder $query, ?string $type): Builder
    {
        return $type === null ? $query : $query->where('auditable_type', $type);
    }

    public function scopeForAuditableId(Builder $query, ?string $id): Builder
    {
        return $id === null ? $query : $query->where('auditable_id', $id);
    }

    public function scopeForUser(Builder $query, ?int $userId): Builder
    {
        return $userId === null ? $query : $query->where('user_id', $userId);
    }

    public function scopeCreatedBetween(Builder $query, ?CarbonInterface $from, ?CarbonInterface $to): Builder
    {
        if ($from !== null) {
            $query->where('created_at', '>=', $from);
        }

        if ($to !== null) {
            $query->where('created_at', '<=', $to);
        }

        return $query;
    }
}
