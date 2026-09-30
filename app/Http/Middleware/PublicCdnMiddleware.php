<?php

namespace App\Http\Middleware;

use App\Models\IPTVConfig;
use Closure;
use Illuminate\Http\Request;

class PublicCdnMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! IPTVConfig::get('URL_CDN')) {
            return response(['Url cdn is disabled.'], 503);
        }

        return $next($request);
    }
}
