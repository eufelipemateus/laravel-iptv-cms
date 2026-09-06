<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuditRestoreService
{
    public function __construct(private readonly AuditPayloadSanitizer $sanitizer) {}

    public function restore(AuditLog $audit): AuditLog
    {
        return DB::transaction(function () use ($audit): AuditLog {
            $source = AuditLog::query()
                ->lockForUpdate()
                ->find($audit->getKey());

            if (! $source || ! $this->canRestore($source)) {
                throw new RuntimeException(__('AUDIT_RESTORE_INVALID'));
            }

            /** @var class-string<Model> $modelClass */
            $modelClass = $source->auditable_type;
            $model = new $modelClass;
            $current = $model->newQuery()
                ->lockForUpdate()
                ->find($source->auditable_id);
            $before = $current?->getAttributes();

            if ($source->event === 'created') {
                if (! $current) {
                    throw new RuntimeException(__('AUDIT_RECORD_NOT_FOUND'));
                }

                $current->deleteQuietly();
                $after = null;
            } elseif ($source->event === 'deleted') {
                if ($current) {
                    throw new RuntimeException(__('AUDIT_RECORD_ALREADY_EXISTS'));
                }

                $current = $model->newInstance();
                $current->forceFill($source->old_values ?? []);
                $current->saveQuietly();
                $after = $current->getAttributes();
            } else {
                if (! $current) {
                    throw new RuntimeException(__('AUDIT_RECORD_NOT_FOUND'));
                }

                $current->forceFill($source->old_values ?? []);
                $current->saveQuietly();
                $after = $current->getAttributes();
            }

            try {
                $restoration = AuditLog::query()->create([
                    'user_id' => auth()->id(),
                    'event' => 'restored',
                    'auditable_type' => $source->auditable_type,
                    'auditable_id' => $source->auditable_id,
                    'old_values' => $this->sanitizer->values($model, $before),
                    'new_values' => $this->sanitizer->values($model, $after),
                    'restored_from_id' => $source->id,
                    ...$this->sanitizer->requestMetadata(app()->runningInConsole() ? null : request()),
                ]);
            } catch (QueryException $exception) {
                if ($this->isDuplicateRestoration($exception)) {
                    throw new RuntimeException(__('AUDIT_RESTORE_INVALID'), 0, $exception);
                }

                throw $exception;
            }

            $source->forceFill(['restored_at' => now()])->saveQuietly();

            return $restoration;
        });
    }

    private function canRestore(AuditLog $audit): bool
    {
        return in_array($audit->event, ['created', 'updated', 'deleted'], true)
            && $audit->restored_at === null
            && in_array($audit->auditable_type, config('audit.models', []), true)
            && class_exists($audit->auditable_type)
            && is_subclass_of($audit->auditable_type, Model::class);
    }

    private function isDuplicateRestoration(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

        return in_array($sqlState, ['23000', '23505'], true)
            && str_contains(strtolower($exception->getMessage()), 'restored_from');
    }
}
