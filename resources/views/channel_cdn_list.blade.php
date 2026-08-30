@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('CDN') }}</h1>
    <a href="{{ route('add_channel_cdn') }}" class="btn btn-sm btn-primary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add CDN') }}
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Slug') }}</th><th>{{ __('Name') }}</th><th>{{ __('Playlist') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($list as $cdn)
                        <tr>
                            <td class="align-middle"><code>{{ $cdn->slug }}</code></td>
                            <td class="align-middle"><strong>{{ $cdn->name }}</strong></td>
                            <td class="align-middle">
                                 @if($show_m3u8_links && $url_cdn && ! $donwload)
                                    <a href="{{ route('cdn-playslit', $cdn->slug) }}" class="btn btn-sm btn-outline-secondary" target="_blank">{{ __('Playlist') }}</a>
                                 @elseif($show_m3u8_links && $url_cdn && $donwload)
                                    <a href="{{ route('cdn-playslit', $cdn->slug) }}" class="btn btn-sm btn-outline-secondary">{{ __('Download') }}</a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle text-right text-nowrap">
                                <a href="{{ route('show_channel_cdn', $cdn->id) }}" class="btn btn-sm btn-outline-primary">{{ __('edit') }}</a>
                                @if($cdn->canDelete())
                                    <form action="{{ route('delete_channel_cdn', $cdn->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this CDN?') }}')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('delete') }}</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">{{ __('No CDNs registered yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($list->hasPages())<div class="mt-4">{{ $list->links('pagination::bootstrap-4') }}</div>@endif
    </div>
</div>
@endsection
