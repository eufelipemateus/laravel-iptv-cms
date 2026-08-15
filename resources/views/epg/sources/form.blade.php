@extends('app')

@section('content')
<h1 class="h3 mb-4 text-gray-800">{{ isset($source) ? 'Edit EPG source' : 'Add EPG source' }}</h1>
@if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
@if($errors->any()) <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div> @endif
<div class="card"><div class="card-body">
<form method="POST" action="{{ isset($source) ? route('epg.sources.update', $source) : route('epg.sources.store') }}">
    @csrf @if(isset($source)) @method('PUT') @endif
    <div class="form-group"><label>Name</label><input class="form-control" name="name" required maxlength="255" value="{{ old('name', $source->name ?? '') }}"></div>
    <div class="form-group"><label>XMLTV URL</label><input class="form-control" type="url" name="url" required value="{{ old('url', $source->url ?? '') }}"></div>
    <input type="hidden" name="format" value="xmltv">
    <div class="form-group"><label>Timezone</label><input class="form-control" name="timezone" required value="{{ old('timezone', $source->timezone ?? config('epg.default_timezone')) }}"></div>
    <div class="form-group"><label>Refresh interval (minutes)</label><input class="form-control" type="number" min="5" max="43200" name="refresh_interval" required value="{{ old('refresh_interval', $source->refresh_interval ?? 360) }}"></div>
    <div class="form-check mb-3"><input type="hidden" name="enabled" value="0"><input class="form-check-input" type="checkbox" name="enabled" value="1" id="enabled" @checked(old('enabled', $source->enabled ?? true))><label class="form-check-label" for="enabled">Enabled</label></div>
    <button class="btn btn-primary">Save</button>
    <a href="{{ route('epg.sources.index') }}" class="btn btn-secondary">Back</a>
</form>
@isset($source)
<hr><form method="POST" action="{{ route('epg.sources.destroy', $source) }}" onsubmit="return confirm('Delete this source and all imported EPG data?')">@csrf @method('DELETE')<button class="btn btn-danger">Delete source</button></form>
@endisset
</div></div>
@endsection
