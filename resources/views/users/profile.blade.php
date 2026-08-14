@extends('app')

@section('content')
<div class="d-sm-flex align-items-center justify-content-between mb-4"><h1 class="h3 mb-0 text-gray-800">{{ __('USERS_PROFILE_TITLE') }}</h1></div>
@if(session('status'))<div class="alert alert-success">{{ session('status') }}</div>@endif
<div class="card shadow mb-4"><div class="card-body">
    <dl class="row mb-0"><dt class="col-sm-3">{{ __('USERS_NAME') }}</dt><dd class="col-sm-9">{{ $user->name }}</dd><dt class="col-sm-3">{{ __('USERS_EMAIL') }}</dt><dd class="col-sm-9">{{ $user->email }}</dd><dt class="col-sm-3">{{ __('USERS_ACCESS') }}</dt><dd class="col-sm-9">{{ $user->is_admin ? __('USERS_ACCESS_ADMIN') : __('USERS_ACCESS_USER') }}</dd></dl>
</div></div>
@endsection
