@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">{{ __('USERS_EDIT_TITLE') }}</h1><a href="{{ route('list_user') }}" class="btn btn-sm btn-secondary">{{ __('USERS_BACK_TO_LIST') }}</a></div>
<div class="card shadow mb-4"><div class="card-body"><form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')
    <div class="form-group"><label for="name">{{ __('USERS_NAME') }}</label><input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $user->name) }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group"><label for="email">{{ __('USERS_EMAIL') }}</label><input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email', $user->email) }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    @if($user->is(auth()->user()))<div class="alert alert-info">{{ __('USERS_SELF_ADMIN_PROTECTED') }}</div>@else
        <div class="custom-control custom-switch mb-4"><input id="is_admin" class="custom-control-input" name="is_admin" value="1" type="checkbox" @checked(old('is_admin', $user->is_admin))><label class="custom-control-label" for="is_admin">{{ __('USERS_GRANT_ADMIN') }}</label></div>
    @endif
    @if(! $user->is_admin)
        <div class="custom-control custom-switch mb-4">
            <input id="active" class="custom-control-input" name="active" value="1" type="checkbox" @checked(old('active', $user->active))>
            <label class="custom-control-label" for="active">{{ __('USERS_ACTIVE') }}</label>
        </div>
    @endif
    <button class="btn btn-primary" type="submit">{{ __('USERS_SAVE') }}</button>
</form></div></div>
@endsection
