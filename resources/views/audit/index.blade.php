@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">{{ __('AUDIT_TITLE') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if($errors->has('restore'))<div class="alert alert-danger">{{ $errors->first('restore') }}</div>@endif
<div class="card shadow mb-4"><div class="card-body">
    <form method="GET" action="{{ route('audit.index') }}" class="mb-4"><div class="input-group">
        <input class="form-control" type="search" name="search" value="{{ $search }}" placeholder="{{ __('AUDIT_SEARCH') }}">
        <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i></button></div>
    </div></form>
    <div class="table-responsive"><table class="table table-hover"><thead><tr><th>{{ __('AUDIT_DATE') }}</th><th>{{ __('AUDIT_USER') }}</th><th>{{ __('AUDIT_EVENT') }}</th><th>{{ __('AUDIT_RESOURCE') }}</th><th>{{ __('AUDIT_CHANGES') }}</th><th></th></tr></thead><tbody>
    @forelse($audits as $audit)<tr>
        <td class="text-nowrap">{{ $audit->created_at?->format('d/m/Y H:i:s') }}</td>
        <td>{{ $audit->user?->name ?? __('AUDIT_SYSTEM') }}</td>
        <td>{{ __('AUDIT_EVENT_'.strtoupper($audit->event)) }}</td>
        <td>{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</td>
        <td><details><summary>{{ __('AUDIT_VIEW_CHANGES') }}</summary><small><strong>{{ __('AUDIT_BEFORE') }}:</strong> {{ json_encode($audit->old_values, JSON_UNESCAPED_UNICODE) }}<br><strong>{{ __('AUDIT_AFTER') }}:</strong> {{ json_encode($audit->new_values, JSON_UNESCAPED_UNICODE) }}</small></details></td>
        <td class="text-right">
            @if($audit->event !== 'restored' && !$audit->restored_at)
            <form method="POST" action="{{ route('audit.restore', $audit) }}" onsubmit="return confirm('{{ __('AUDIT_RESTORE_CONFIRM') }}')">@csrf<button class="btn btn-sm btn-outline-warning" type="submit">{{ __('AUDIT_RESTORE') }}</button></form>
            @elseif($audit->restored_at)<span class="badge badge-success">{{ __('AUDIT_ALREADY_RESTORED') }}</span>@endif
        </td>
    </tr>@empty<tr><td colspan="6" class="text-center text-muted">{{ __('AUDIT_EMPTY') }}</td></tr>@endforelse
    </tbody></table></div>
    {{ $audits->links() }}
</div></div>
@endsection
