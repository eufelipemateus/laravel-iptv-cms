<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Services\Audit\AuditPayloadSanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class AuditObserver
{
    private static bool $auditTableAvailable = false;

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
            $old[$attribute] = $model->getRawOriginal($attribute);
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

        if (! $this->auditTableIsAvailable()) {
            return;
        }

        try {
            AuditLog::query()->create([
                'user_id' => auth()->id(),
                'event' => $event,
                'auditable_type' => $model::class,
                'auditable_id' => (string) $model->getKey(),
                'old_values' => $old,
                'new_values' => $new,
                ...$this->sanitizer->requestMetadata($request),
            ]);
        } catch (QueryException $exception) {
            if (! $this->isMissingAuditTable($exception)) {
                throw $exception;
            }

            self::$auditTableAvailable = false;
        }
    }

    private function auditTableIsAvailable(): bool
    {
        if (self::$auditTableAvailable) {
            return true;
        }

        return self::$auditTableAvailable = Schema::hasTable((new AuditLog)->getTable());
    }

    private function isMissingAuditTable(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        return str_contains($message, 'audit_logs')
            && (in_array($sqlState, ['42P01', '42S02'], true)
                || in_array($driverCode, [1, 1146], true));
    }
}
