@extends('app')
@section('content')
<h1 class="h3 mb-4 text-gray-800">EPG Programmes</h1>
<div class="card"><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>Source</th><th>Channel</th><th>Title</th><th>Category</th><th>Start</th><th>End</th></tr></thead><tbody>
@forelse($programmes as $programme)<tr><td>{{ $programme->channel->source->name }}</td><td>{{ $programme->channel->display_name }}</td><td>{{ $programme->title }}</td><td>{{ $programme->category ?: '—' }}</td><td>{{ $programme->start_at }}</td><td>{{ $programme->end_at }}</td></tr>@empty<tr><td colspan="6">No imported programmes.</td></tr>@endforelse
</tbody></table>{{ $programmes->links('pagination::bootstrap-4') }}</div></div>
@endsection
