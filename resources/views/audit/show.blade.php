@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('AUDIT_DETAILS') }} #{{ $auditLog->id }}</h1>
    <a class="btn btn-outline-secondary" href="{{ route('audit.index') }}">{{ __('AUDIT_BACK') }}</a>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->has('restore'))
    <div class="alert alert-danger">{{ $errors->first('restore') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-body">
        <dl class="row">
            <dt class="col-sm-3">{{ __('AUDIT_ID') }}</dt>
            <dd class="col-sm-9">{{ $auditLog->id }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_EVENT') }}</dt>
            <dd class="col-sm-9">{{ __('AUDIT_EVENT_'.strtoupper($auditLog->event)) }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_RESOURCE') }}</dt>
            <dd class="col-sm-9">{{ class_basename($auditLog->auditable_type) }} #{{ $auditLog->auditable_id }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_USER') }}</dt>
            <dd class="col-sm-9">{{ $auditLog->user?->name ?? __('AUDIT_SYSTEM') }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_DATE') }}</dt>
            <dd class="col-sm-9">{{ $auditLog->created_at?->format('d/m/Y H:i:s') }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_URL') }}</dt>
            <dd class="col-sm-9 text-break">{{ $auditLog->url ?? __('AUDIT_NONE') }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_IP_ADDRESS') }}</dt>
            <dd class="col-sm-9">{{ $auditLog->ip_address ?? __('AUDIT_NONE') }}</dd>
            <dt class="col-sm-3">{{ __('AUDIT_USER_AGENT') }}</dt>
            <dd class="col-sm-9 text-break">{{ $auditLog->user_agent ?? __('AUDIT_NONE') }}</dd>
            @if($auditLog->restored_at)
                <dt class="col-sm-3">{{ __('AUDIT_RESTORED_AT') }}</dt>
                <dd class="col-sm-9">{{ $auditLog->restored_at->format('d/m/Y H:i:s') }}</dd>
            @endif
        </dl>

        <h2 class="h5">{{ __('AUDIT_BEFORE') }}</h2>
        <pre class="bg-light border rounded p-3">{{ json_encode($auditLog->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>

        <h2 class="h5">{{ __('AUDIT_AFTER') }}</h2>
        <pre class="bg-light border rounded p-3">{{ json_encode($auditLog->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>

        @if($auditLog->restoredFrom)
            <p>
                <strong>{{ __('AUDIT_RESTORED_FROM') }}:</strong>
                <a href="{{ route('audit.show', $auditLog->restoredFrom) }}">#{{ $auditLog->restoredFrom->id }}</a>
            </p>
        @endif

        @if($auditLog->restoration)
            <p>
                <strong>{{ __('AUDIT_RESTORATION_ENTRY') }}:</strong>
                <a href="{{ route('audit.show', $auditLog->restoration) }}">#{{ $auditLog->restoration->id }}</a>
            </p>
        @endif

        @if($auditLog->event !== 'restored' && ! $auditLog->restored_at)
            <form method="POST" action="{{ route('audit.restore', $auditLog) }}" onsubmit="return confirm('{{ __('AUDIT_RESTORE_CONFIRM') }}')">
                @csrf
                <button class="btn btn-outline-warning" type="submit">{{ __('AUDIT_RESTORE') }}</button>
            </form>
        @elseif($auditLog->restored_at)
            <span class="badge badge-success">{{ __('AUDIT_ALREADY_RESTORED') }}</span>
        @endif
    </div>
</div>
@endsection
