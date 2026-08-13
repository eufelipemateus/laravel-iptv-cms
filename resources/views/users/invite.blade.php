@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">{{ __('USERS_INVITE_TITLE') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="card shadow mb-4"><div class="card-body"><form method="POST" action="{{ route('users.invite.store') }}">@csrf
    <div class="form-group"><label for="name">{{ __('USERS_NAME') }}</label><input id="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="form-group"><label for="email">{{ __('USERS_EMAIL') }}</label><input id="email" class="form-control @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email') }}" required>@error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
    <div class="custom-control custom-checkbox mb-4"><input id="is_admin" class="custom-control-input" name="is_admin" value="1" type="checkbox" @checked(old('is_admin'))><label class="custom-control-label" for="is_admin">{{ __('USERS_GRANT_ADMIN') }}</label></div>
    <button class="btn btn-primary" type="submit">{{ __('USERS_SEND_INVITATION') }}</button>
</form></div></div>
@endsection
