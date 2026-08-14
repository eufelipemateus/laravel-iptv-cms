@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Videos') }}</h1>
    <a href="{{ route('vods.new') }}" class="btn btn-sm btn-primary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add video') }}
    </a>
</div>

@if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card">
    <div class="card-body">
        <form method="GET" action="{{ route('vods.list') }}" class="mb-4">
            <div class="input-group">
                <input
                    type="search"
                    class="form-control"
                    name="search"
                    value="{{ $search }}"
                    placeholder="{{ __('Search videos') }}"
                    aria-label="{{ __('Search videos') }}"
                >
                <div class="input-group-append">
                    <button class="btn btn-primary">{{ __('Search') }}</button>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Title') }}</th>
                        <th>{{ __('Video file') }}</th>
                        <th>{{ __('Updated') }}</th>
                        <th class="text-right">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($list as $vod)
                        <tr>
                            <td>
                                <strong>{{ $vod->name }}</strong>
                                @if($vod->description)
                                    <br><small class="text-muted">{{ \Illuminate\Support\Str::limit($vod->description, 80) }}</small>
                                @endif
                            </td>
                            <td>{{ $vod->original_filename ?: __('No file') }}</td>
                            <td>{{ $vod->updated_at?->format('d/m/Y H:i') }}</td>
                            <td class="text-right text-nowrap">
                                @if($vod->is_playable)
                                    <a href="{{ route('vods.stream', $vod->id) }}" class="btn btn-sm btn-outline-secondary" target="_blank">{{ __('Play') }}</a>
                                @endif
                                <a href="{{ route('vods.edit', $vod->id) }}" class="btn btn-sm btn-outline-primary">{{ __('Edit') }}</a>
                                <form method="POST" action="{{ route('vods.delete', $vod->id) }}" class="d-inline" onsubmit="return confirm('{{ __('Delete this video?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('Delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">{{ __('No videos registered yet.') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-3">{{ $list->links() }}</div>
    </div>
</div>
@endsection
