<?php

namespace App\Services\Audit;

use App\Exceptions\AuditRestoreException;
use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AuditRestoreService
{
    public function __construct(
        private readonly AuditPayloadSanitizer $sanitizer,
        private readonly AuditSnapshotComparator $comparator,
    ) {}

    public function canRestore(AuditLog $audit): bool
    {
        return $audit->canBeRestored();
    }

    /**
     * @throws AuditRestoreException
     */
    public function restore(AuditLog $audit): AuditLog
    {
        try {
            return DB::transaction(function () use ($audit): AuditLog {
                $source = AuditLog::query()
                    ->lockForUpdate()
                    ->find($audit->getKey());

                if (! $source || ! $this->canRestore($source)) {
                    throw new AuditRestoreException(__('AUDIT_RESTORE_INVALID'));
                }

                /** @var class-string<Model> $modelClass */
                $modelClass = $source->auditable_type;
                $model = new $modelClass;
                $current = $model->newQuery()
                    ->lockForUpdate()
                    ->find($source->auditable_id);
                $before = $current?->getAttributes();

                if ($source->event === 'created') {
                    $after = $this->revertCreatedRecord($source, $current);
                } elseif ($source->event === 'deleted') {
                    $after = $this->restoreDeletedRecord($source, $model, $current);
                } else {
                    $after = $this->restoreUpdatedRecord($source, $current);
                }

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

                $source->forceFill(['restored_at' => now()])->saveQuietly();

                return $restoration;
            });
        } catch (AuditRestoreException $exception) {
            throw $exception;
        } catch (QueryException $exception) {
            if ($this->isDuplicateRestoration($exception)) {
                throw new AuditRestoreException(__('AUDIT_RESTORE_INVALID'), 0, $exception);
            }

            if (! $this->isExpectedDatabaseFailure($exception)) {
                throw $exception;
            }

            report($exception);

            throw new AuditRestoreException(__('AUDIT_RESTORE_FAILED'), 0, $exception);
        }
    }

    private function revertCreatedRecord(AuditLog $source, ?Model $current): null
    {
        if (! $current) {
            throw new AuditRestoreException(__('AUDIT_RECORD_NOT_FOUND'));
        }

        if (! $this->comparator->matches($current, $source->new_values ?? [])
            || $this->hasLaterAudit($source)) {
            throw new AuditRestoreException(__('AUDIT_RESTORE_CONFLICT'));
        }

        $current->deleteQuietly();

        return null;
    }

    /** @return array<string, mixed> */
    private function restoreDeletedRecord(AuditLog $source, Model $model, ?Model $current): array
    {
        if ($current) {
            throw new AuditRestoreException(__('AUDIT_RECORD_ALREADY_EXISTS'));
        }

        $restored = $model->newInstance();
        $restored->forceFill($source->old_values ?? []);
        $restored->saveQuietly();

        return $restored->getAttributes();
    }

    /** @return array<string, mixed> */
    private function restoreUpdatedRecord(AuditLog $source, ?Model $current): array
    {
        if (! $current) {
            throw new AuditRestoreException(__('AUDIT_RECORD_NOT_FOUND'));
        }

        if (! $this->comparator->matches($current, $source->new_values ?? [])) {
            throw new AuditRestoreException(__('AUDIT_RESTORE_CONFLICT'));
        }

        $current->forceFill($source->old_values ?? []);
        $current->saveQuietly();

        return $current->getAttributes();
    }

    private function hasLaterAudit(AuditLog $source): bool
    {
        return AuditLog::query()
            ->where('auditable_type', $source->auditable_type)
            ->where('auditable_id', $source->auditable_id)
            ->whereKey('>', $source->getKey())
            ->exists();
    }

    private function isDuplicateRestoration(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());

        return str_starts_with($sqlState, '23')
            && str_contains(strtolower($exception->getMessage()), 'restored_from');
    }

    private function isExpectedDatabaseFailure(QueryException $exception): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? $exception->getCode());
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return str_starts_with($sqlState, '22')
            || str_starts_with($sqlState, '23')
            || in_array($driverCode, [19, 1048, 1062, 1264, 1364, 1406, 1451, 1452], true);
    }
}
