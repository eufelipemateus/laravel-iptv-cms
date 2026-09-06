<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AuditRestoreService
{
    private const HIDDEN_ATTRIBUTES = [
        'password', 'remember_token', 'invitation_token', 'api_token', 'access_token',
    ];

    public function restore(AuditLog $audit): AuditLog
    {
        if ($audit->event === 'restored' || $audit->restored_at) {
            throw new RuntimeException(__('AUDIT_RESTORE_INVALID'));
        }

        if (! class_exists($audit->auditable_type) || ! is_subclass_of($audit->auditable_type, Model::class)) {
            throw new RuntimeException(__('AUDIT_RESTORE_INVALID'));
        }

        return DB::transaction(function () use ($audit): AuditLog {
            /** @var Model $model */
            $model = new $audit->auditable_type;
            $current = $model->newQuery()->find($audit->auditable_id);
            $before = $current?->getAttributes();

            if ($audit->event === 'created') {
                if (! $current) {
                    throw new RuntimeException(__('AUDIT_RECORD_NOT_FOUND'));
                }
                $current->deleteQuietly();
                $after = null;
            } elseif ($audit->event === 'deleted') {
                if ($current) {
                    throw new RuntimeException(__('AUDIT_RECORD_ALREADY_EXISTS'));
                }
                $current = $model->newInstance();
                $current->forceFill($audit->old_values ?? []);
                $current->saveQuietly();
                $after = $current->getAttributes();
            } else {
                if (! $current) {
                    throw new RuntimeException(__('AUDIT_RECORD_NOT_FOUND'));
                }
                $current->forceFill($audit->old_values ?? []);
                $current->saveQuietly();
                $after = $current->getAttributes();
            }

            $restoration = AuditLog::query()->create([
                'user_id' => auth()->id(),
                'event' => 'restored',
                'auditable_type' => $audit->auditable_type,
                'auditable_id' => $audit->auditable_id,
                'old_values' => $this->withoutSecrets($before),
                'new_values' => $this->withoutSecrets($after),
                'restored_from_id' => $audit->id,
                'url' => request()->fullUrl(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            $audit->forceFill(['restored_at' => now()])->saveQuietly();

            return $restoration;
        });
    }

    private function withoutSecrets(?array $values): ?array
    {
        return $values === null
            ? null
            : array_diff_key($values, array_flip(self::HIDDEN_ATTRIBUTES));
    }
}
