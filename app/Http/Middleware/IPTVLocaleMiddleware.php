<?php

namespace App\Http\Middleware;

use App\Helpers\Locale;
use App\Models\IPTVConfig;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

class IPTVLocaleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $locale = IPTVConfig::get('CURRENT_LOCALE', 'br');
        if (! array_key_exists($locale, Locale::getList())) {
            $locale = config('app.fallback_locale', 'en');
        }

        App::setLocale($locale);

        return $next($request);
    }
}
