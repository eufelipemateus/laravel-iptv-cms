<?php

namespace App\Http\Middleware;

use App\Actions\Users\LogoutUserAction;
use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->user() || ! $request->user()->active) {
            LogoutUserAction::run($request);

            return redirect()->route('login');
        }

        return $next($request);
    }
}
