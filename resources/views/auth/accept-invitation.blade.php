<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><title>{{ __('AUTH_SET_PASSWORD_TITLE') }} · IPTV</title><link href="/assets/css/sb-admin-2.min.css" rel="stylesheet"></head>
<body class="bg-gradient-primary"><div class="container"><div class="row justify-content-center"><div class="col-xl-5 col-lg-6 col-md-8"><div class="card o-hidden border-0 shadow-lg my-5"><div class="card-body p-5">
    <h1 class="h4 text-gray-900 mb-2 text-center">{{ __('AUTH_WELCOME_USER', ['name' => $user->name]) }}</h1><p class="text-center text-muted mb-4">{{ __('AUTH_SET_PASSWORD_DESCRIPTION') }}</p>
    <form method="POST" action="{{ route('invitation.accept', $token) }}">@csrf
        <div class="form-group"><input class="form-control form-control-user @error('password') is-invalid @enderror" type="password" name="password" placeholder="{{ __('AUTH_NEW_PASSWORD') }}" required autofocus>@error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
        <div class="form-group"><input class="form-control form-control-user" type="password" name="password_confirmation" placeholder="{{ __('AUTH_CONFIRM_PASSWORD') }}" required></div>
        <button class="btn btn-primary btn-user btn-block" type="submit">{{ __('AUTH_ACTIVATE_ACCOUNT') }}</button>
    </form>
</div></div></div></div></div></body></html>
