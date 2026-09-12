<?php

namespace App\Http\Controllers;

use App\Exceptions\AuditRestoreException;
use App\Http\Requests\AuditLogIndexRequest;
use App\Models\AuditLog;
use App\Services\Audit\AuditRestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): View
    {
        $filters = $request->validated();
        $dateFrom = isset($filters['date_from']) ? Carbon::parse($filters['date_from'])->startOfDay() : null;
        $dateTo = isset($filters['date_to']) ? Carbon::parse($filters['date_to'])->endOfDay() : null;

        $audits = AuditLog::query()
            ->select([
                'id',
                'user_id',
                'event',
                'auditable_type',
                'auditable_id',
                'restored_from_id',
                'restored_at',
                'created_at',
            ])
            ->with('user:id,name')
            ->forEvent($filters['event'] ?? null)
            ->forAuditableType($filters['auditable_type'] ?? null)
            ->forAuditableId($filters['auditable_id'] ?? null)
            ->forUser(isset($filters['user_id']) ? (int) $filters['user_id'] : null)
            ->createdBetween($dateFrom, $dateTo)
            ->orderByDesc('id')
            ->cursorPaginate(25)
            ->withQueryString();

        $auditableModels = collect(config('audit.models', []))
            ->mapWithKeys(fn (string $model): array => [$model => class_basename($model)])
            ->all();

        return view('audit.index', compact('audits', 'filters', 'auditableModels'));
    }

    public function show(AuditLog $auditLog, AuditRestoreService $service): View
    {
        $auditLog->load(['user:id,name', 'restoredFrom:id,event', 'restoration:id,restored_from_id']);
        $canRestore = $service->canRestore($auditLog);

        return view('audit.show', compact('auditLog', 'canRestore'));
    }

    public function restore(AuditLog $auditLog, AuditRestoreService $service): RedirectResponse
    {
        try {
            $service->restore($auditLog);
        } catch (AuditRestoreException $exception) {
            return back()->withErrors(['restore' => $exception->getMessage()]);
        }

        return back()->with('status', __('AUDIT_RESTORED'));
    }
}
