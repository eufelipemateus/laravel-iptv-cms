<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('AUTH_LOGIN_TITLE') }} · IPTV</title>
    <link href="/assets/css/sb-admin-2.min.css" rel="stylesheet">
</head>
<body class="bg-gradient-primary">
<div class="container min-vh-100"><div class="row justify-content-center align-items-center min-vh-100"><div class="col-xl-5 col-lg-6 col-md-8">
    <div class="card o-hidden border-0 shadow-lg"><div class="card-body p-5">
        <h1 class="h4 text-gray-900 mb-4 text-center">{{ __('AUTH_LOGIN_HEADING') }}</h1>
        @if (app()->isStore())
            <div class="alert alert-info small" role="alert">
                <strong>{{ __('AUTH_STORE_MODE_LABEL') }}</strong>
                {{ __('AUTH_STORE_MODE_CREDENTIALS', ['email' => \App\Models\User::STORE_DEMO_EMAIL, 'password' => \App\Models\User::STORE_DEMO_PASSWORD]) }}
            </div>
        @endif
        <form method="POST" action="{{ route('login.store') }}">@csrf
            <div class="form-group"><input class="form-control form-control-user @error('email') is-invalid @enderror" name="email" type="email" value="{{ old('email') }}" placeholder="{{ __('AUTH_EMAIL') }}" required autofocus>
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="form-group"><input class="form-control form-control-user @error('password') is-invalid @enderror" name="password" type="password" placeholder="{{ __('AUTH_PASSWORD') }}" required></div>
            <div class="form-group"><div class="custom-control custom-checkbox small"><input class="custom-control-input" name="remember" value="1" type="checkbox" id="remember"><label class="custom-control-label" for="remember">{{ __('AUTH_REMEMBER_ME') }}</label></div></div>
            <button class="btn btn-primary btn-user btn-block" type="submit">{{ __('AUTH_LOGIN_BUTTON') }}</button>
        </form>
    </div></div>
</div></div></div>
</body></html>
