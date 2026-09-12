@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('AUDIT_TITLE') }}</h1>
</div>

@if(session('status'))
    <div class="alert alert-success">{{ session('status') }}</div>
@endif

@if($errors->has('restore'))
    <div class="alert alert-danger">{{ $errors->first('restore') }}</div>
@endif

<div class="card shadow mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('audit.index') }}" class="mb-4">
            <div class="form-row">
                <div class="form-group col-md-2">
                    <label for="audit-event">{{ __('AUDIT_EVENT') }}</label>
                    <select class="form-control" id="audit-event" name="event">
                        <option value="">{{ __('AUDIT_ALL') }}</option>
                        @foreach(\App\Models\AuditLog::EVENTS as $event)
                            <option value="{{ $event }}" @selected(($filters['event'] ?? '') === $event)>
                                {{ __('AUDIT_EVENT_'.strtoupper($event)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-3">
                    <label for="audit-resource">{{ __('AUDIT_RESOURCE') }}</label>
                    <select class="form-control" id="audit-resource" name="auditable_type">
                        <option value="">{{ __('AUDIT_ALL') }}</option>
                        @foreach($auditableModels as $model => $label)
                            <option value="{{ $model }}" @selected(($filters['auditable_type'] ?? '') === $model)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group col-md-2">
                    <label for="audit-resource-id">{{ __('AUDIT_RESOURCE_ID') }}</label>
                    <input class="form-control" id="audit-resource-id" name="auditable_id" value="{{ $filters['auditable_id'] ?? '' }}">
                </div>
                <div class="form-group col-md-2">
                    <label for="audit-user-id">{{ __('AUDIT_USER_ID') }}</label>
                    <input class="form-control" id="audit-user-id" name="user_id" type="number" min="1" value="{{ $filters['user_id'] ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="audit-date-from">{{ __('AUDIT_DATE_FROM') }}</label>
                    <input class="form-control" id="audit-date-from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="form-group col-md-3">
                    <label for="audit-date-to">{{ __('AUDIT_DATE_TO') }}</label>
                    <input class="form-control" id="audit-date-to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">
                </div>
            </div>
            <button class="btn btn-primary" type="submit">{{ __('AUDIT_FILTER') }}</button>
            <a class="btn btn-outline-secondary" href="{{ route('audit.index') }}">{{ __('AUDIT_CLEAR') }}</a>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>{{ __('AUDIT_DATE') }}</th>
                        <th>{{ __('AUDIT_USER') }}</th>
                        <th>{{ __('AUDIT_EVENT') }}</th>
                        <th>{{ __('AUDIT_RESOURCE') }}</th>
                        <th>{{ __('AUDIT_CHANGES') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($audits as $audit)
                        <tr>
                            <td class="text-nowrap">{{ $audit->created_at?->format('d/m/Y H:i:s') }}</td>
                            <td>{{ $audit->user?->name ?? __('AUDIT_SYSTEM') }}</td>
                            <td>{{ __('AUDIT_EVENT_'.strtoupper($audit->event)) }}</td>
                            <td>{{ class_basename($audit->auditable_type) }} #{{ $audit->auditable_id }}</td>
                            <td>
                                <a href="{{ route('audit.show', $audit) }}">{{ __('AUDIT_VIEW_CHANGES') }}</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">{{ __('AUDIT_EMPTY') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $audits->links() }}
    </div>
</div>
@endsection
