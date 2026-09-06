<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\Audit\AuditPayloadSanitizer;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function __construct(private readonly AuditPayloadSanitizer $sanitizer) {}

    public function created(Model $model): void
    {
        $this->record($model, 'created', null, $model->getAttributes());
    }

    public function updated(Model $model): void
    {
        $changes = $model->getChanges();
        unset($changes['updated_at']);

        if ($changes === []) {
            return;
        }

        $old = [];
        foreach (array_keys($changes) as $attribute) {
            $old[$attribute] = $model->getOriginal($attribute);
        }

        $this->record($model, 'updated', $old, $changes);
    }

    public function deleted(Model $model): void
    {
        $this->record($model, 'deleted', $model->getAttributes(), null);
    }

    private function record(Model $model, string $event, ?array $old, ?array $new): void
    {
        $request = app()->runningInConsole() ? null : request();
        $old = $this->sanitizer->values($model, $old);
        $new = $this->sanitizer->values($model, $new);

        if ($event === 'updated' && $old === [] && $new === []) {
            return;
        }

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => (string) $model->getKey(),
            'old_values' => $old,
            'new_values' => $new,
            ...$this->sanitizer->requestMetadata($request),
        ]);
    }
}
