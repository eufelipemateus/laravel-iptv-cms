<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureVodModuleIsEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('modules.vod.enabled', false), 404);

        return $next($request);
    }
}
