@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4">
    <h1 class="h3 mb-0 text-gray-800">{{ __('USERS_LIST_TITLE') }}</h1>
    @if (!(app()->hasMacro('isStore') && app()->isStore()))
        <a href="{{ route('users.invite') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-user-plus fa-sm text-white-50"></i> {{ __('USERS_INVITE_TITLE') }}</a>
    @endif
</div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
@if (app()->hasMacro('isStore') && app()->isStore())
    <div class="alert alert-info">{{ __('USERS_STORE_READ_ONLY') }}</div>
@endif
<div class="card shadow mb-4"><div class="card-body">
    <form method="GET" action="{{ route('list_user') }}" class="mb-4"><div class="input-group">
        <input class="form-control" type="search" name="search" value="{{ $search }}" placeholder="{{ __('USERS_SEARCH') }}" aria-label="{{ __('USERS_SEARCH') }}">
        <div class="input-group-append"><button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> {{ __('USERS_SEARCH') }}</button></div>
    </div></form>
    <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>{{ __('USERS_NAME') }}</th><th>{{ __('USERS_EMAIL') }}</th><th>{{ __('USERS_ACCESS') }}</th><th>{{ __('USERS_STATUS') }}</th><th class="text-right">{{ __('USERS_ACTIONS') }}</th></tr></thead><tbody>
        @forelse($users as $user)<tr>
            <td>{{ $user->name }}</td><td>{{ $user->email }}</td><td>{{ $user->is_admin ? __('USERS_ACCESS_ADMIN') : __('USERS_ACCESS_USER') }}</td>
            <td>
                @if (! $user->active)
                    {{ __('USERS_STATUS_INACTIVE') }}
                @elseif ($user->invitation_token)
                    {{ __('USERS_STATUS_INVITED') }}
                @else
                    {{ __('USERS_STATUS_ACTIVE') }}
                @endif
            </td>
            <td class="text-right">
                @if (app()->hasMacro('isStore') && app()->isStore())
                    <a href="{{ route('users.show', $user) }}" class="btn btn-sm btn-outline-secondary">{{ __('USERS_VIEW') }}</a>
                @else
                    <a href="{{ route('users.edit', $user) }}" class="btn btn-sm btn-outline-primary">{{ __('USERS_EDIT') }}</a>
                @endif
            </td>
        </tr>@empty<tr><td colspan="5" class="text-center text-muted">{{ __('USERS_NONE_FOUND') }}</td></tr>@endforelse
    </tbody></table></div>
    @if($users->hasPages())<div class="mt-4">{{ $users->links() }}</div>@endif
</div></div>
@endsection
