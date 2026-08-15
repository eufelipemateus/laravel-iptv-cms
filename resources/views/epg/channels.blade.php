@extends('app')
@section('content')
<h1 class="h3 mb-4 text-gray-800">EPG Channels</h1>
<form class="form-inline mb-3"><select class="form-control mr-2" name="source_id"><option value="">All sources</option>@foreach($sources as $source)<option value="{{ $source->id }}" @selected(request('source_id') == $source->id)>{{ $source->name }}</option>@endforeach</select><input class="form-control mr-2" name="q" value="{{ request('q') }}" placeholder="Name or external ID"><button class="btn btn-primary">Search</button></form>
<div class="card"><div class="card-body table-responsive"><table class="table table-striped"><thead><tr><th>Source</th><th>External ID</th><th>Name</th><th>Language</th><th>Mapped IPTV channels</th></tr></thead><tbody>
@forelse($channels as $channel)<tr><td>{{ $channel->source->name }}</td><td>{{ $channel->external_id }}</td><td>{{ $channel->display_name }}</td><td>{{ $channel->language ?: '—' }}</td><td>{{ $channel->channels()->count() }}</td></tr>@empty<tr><td colspan="5">No imported channels.</td></tr>@endforelse
</tbody></table>{{ $channels->links('pagination::bootstrap-4') }}</div></div>
@endsection
