<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('AUTH_LOGIN_TITLE') }} · IPTV</title>
    <link href="/assets/css/sb-admin-2.min.css" rel="stylesheet">
    <style>
        .demo-credentials {
            border: 0;
            border-left: .25rem solid #36b9cc;
            padding: 1rem 1.25rem;
        }

        .demo-credentials__title {
            color: #2c7a86;
            font-size: .875rem;
            font-weight: 700;
            margin-bottom: .75rem;
        }

        .demo-credentials__list {
            display: grid;
            gap: .5rem;
            margin: 0;
        }

        .demo-credentials__item {
            align-items: center;
            display: grid;
            gap: .25rem .75rem;
            grid-template-columns: 4.5rem minmax(0, 1fr);
        }

        .demo-credentials__item dt,
        .demo-credentials__item dd {
            margin: 0;
        }

        .demo-credentials__item dt {
            color: #5a5c69;
            font-weight: 600;
        }

        .demo-credentials__value {
            background: rgba(255, 255, 255, .7);
            border: 1px solid rgba(54, 185, 204, .25);
            border-radius: .35rem;
            color: #2e3a3d;
            display: block;
            font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", monospace;
            font-size: .8125rem;
            overflow-wrap: anywhere;
            padding: .35rem .55rem;
            user-select: all;
        }
    </style>
</head>
<body class="bg-gradient-primary">
<div class="container min-vh-100"><div class="row justify-content-center align-items-center min-vh-100"><div class="col-xl-5 col-lg-6 col-md-8">
    <div class="card o-hidden border-0 shadow-lg"><div class="card-body p-5">
        <h1 class="h4 text-gray-900 mb-4 text-center">{{ __('AUTH_LOGIN_HEADING') }}</h1>
        @if (app()->isStore())
            <div class="alert alert-info demo-credentials" role="note" aria-labelledby="demo-credentials-title">
                <div class="demo-credentials__title" id="demo-credentials-title">
                    {{ __('AUTH_STORE_MODE_LABEL') }}
                </div>
                <dl class="demo-credentials__list small">
                    <div class="demo-credentials__item">
                        <dt>{{ __('AUTH_EMAIL') }}</dt>
                        <dd><code class="demo-credentials__value">{{ \App\Models\User::STORE_DEMO_EMAIL }}</code></dd>
                    </div>
                    <div class="demo-credentials__item">
                        <dt>{{ __('AUTH_PASSWORD') }}</dt>
                        <dd><code class="demo-credentials__value">{{ \App\Models\User::STORE_DEMO_PASSWORD }}</code></dd>
                    </div>
                </dl>
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
