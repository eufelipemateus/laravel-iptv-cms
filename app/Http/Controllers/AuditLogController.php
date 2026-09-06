<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Services\Audit\AuditRestoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use RuntimeException;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $audits = AuditLog::query()
            ->with('user')
            ->when($search !== '', fn ($query) => $query->where(function ($query) use ($search): void {
                $query->where('auditable_type', 'like', "%{$search}%")
                    ->orWhere('auditable_id', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$search}%"));
            }))
            ->latest()->paginate(25)->withQueryString();

        return view('audit.index', compact('audits', 'search'));
    }

    public function restore(AuditLog $auditLog, AuditRestoreService $service): RedirectResponse
    {
        try {
            $service->restore($auditLog);
        } catch (RuntimeException $exception) {
            return back()->withErrors(['restore' => $exception->getMessage()]);
        }

        return back()->with('status', __('AUDIT_RESTORED'));
    }
}
