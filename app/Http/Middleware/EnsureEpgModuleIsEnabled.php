<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureEpgModuleIsEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('modules.epg.enabled', true), 404);

        return $next($request);
    }
}
