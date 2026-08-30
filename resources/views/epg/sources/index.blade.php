@extends('app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 text-gray-800">EPG Sources</h1>
    <a href="{{ route('epg.sources.create') }}" class="btn btn-primary">Add source</a>
</div>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert-danger">{{ $errors->first() }}</div> @endif
<div class="card"><div class="card-body table-responsive">
<table class="table table-striped">
    <thead><tr><th>Name</th><th>URL</th><th>Status</th><th>Channels</th><th>Programmes</th><th>Last sync</th><th>Last success</th><th>Error</th><th></th></tr></thead>
    <tbody>
    @forelse($sources as $source)
        <tr>
            <td><a href="{{ route('epg.sources.edit', $source) }}">{{ $source->name }}</a></td>
            <td class="text-truncate" style="max-width:220px" title="{{ $source->url }}">{{ $source->url }}</td>
            <td>{{ $source->enabled ? 'Enabled' : 'Disabled' }}</td>
            <td>{{ $source->channels_count }}</td><td>{{ $source->programmes_count }}</td>
            <td>{{ optional($source->last_sync_at)->toDateTimeString() ?: 'Never' }}</td>
            <td>{{ optional($source->last_success_at)->toDateTimeString() ?: 'Never' }}</td>
            <td class="text-danger" title="{{ $source->last_error }}">{{ $source->last_error ? \Illuminate\Support\Str::limit($source->last_error, 60) : '—' }}</td>
            <td><form method="POST" action="{{ route('epg.sources.sync', $source) }}">@csrf<button class="btn btn-sm btn-success">Sync now</button></form></td>
        </tr>
    @empty <tr><td colspan="9">No EPG sources configured.</td></tr> @endforelse
    </tbody>
</table>
{{ $sources->links() }}
</div></div>
@endsection
