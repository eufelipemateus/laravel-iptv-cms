<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCustomerModuleIsEnabled
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('modules.customer.enabled', false), 404);

        return $next($request);
    }
}
