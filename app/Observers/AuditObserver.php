<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class AuditObserver
{
    private const HIDDEN_ATTRIBUTES = [
        'password', 'remember_token', 'invitation_token', 'api_token', 'access_token',
    ];

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
        // Some legacy migrations create models before this table itself exists.
        if (app()->runningInConsole() && ! Schema::hasTable('audit_logs')) {
            return;
        }

        $request = app()->runningInConsole() ? null : request();

        AuditLog::query()->create([
            'user_id' => auth()->id(),
            'event' => $event,
            'auditable_type' => $model::class,
            'auditable_id' => (string) $model->getKey(),
            'old_values' => $this->withoutSecrets($old),
            'new_values' => $this->withoutSecrets($new),
            'url' => $request?->fullUrl(),
            'ip_address' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]);
    }

    private function withoutSecrets(?array $values): ?array
    {
        if ($values === null) {
            return null;
        }

        return array_diff_key($values, array_flip(self::HIDDEN_ATTRIBUTES));
    }
}
