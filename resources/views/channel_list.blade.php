@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('Channels') }}</h1>
    <a href="{{ route('add_channel') }}" class="btn btn-sm btn-primary shadow-sm mt-3 mt-sm-0">
        <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add Channel') }}
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead><tr><th>{{ __('Number') }}</th><th>{{ __('Logo') }}</th><th>{{ __('Name') }}</th><th>{{ __('Group') }}</th><th class="text-right">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse($list as $channel)
                        <tr>
                            <td class="align-middle"><strong>{{ $channel->number }}</strong></td>
                            <td class="align-middle">
                                @if($channel->logo)
                                    <img src="{{ url($channel->logo) }}" width="50" height="50" class="img-thumbnail" alt="{{ $channel->name }}">
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="align-middle">{{ $channel->name }}</td>
                            <td class="align-middle">{{ $channel->group?->name ?: '—' }}</td>
                            <td class="align-middle text-right text-nowrap">
                                <a href="{{ route('show_channel', $channel->id) }}" class="btn btn-sm btn-outline-primary">{{ __('edit') }}</a>
                                <form action="{{ route('delete_channel', $channel->id) }}" method="POST" class="d-inline" onsubmit="return confirm('{{ __('Delete this channel?') }}')">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger">{{ __('delete') }}</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">{{ __('No channels registered yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($list->hasPages())<div class="mt-4">{{ $list->links('pagination::bootstrap-4') }}</div>@endif
    </div>
</div>
@endsection
