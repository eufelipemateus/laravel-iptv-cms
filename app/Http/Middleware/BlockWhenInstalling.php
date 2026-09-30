<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockWhenInstalling
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! app()->environment('install')) {
            return $next($request);
        }

        if ($request->is('up')) {
            return $next($request);
        }

        $message = 'Application is in installation mode. Run: php artisan install';

        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => $message,
            ], Response::HTTP_LOCKED);
        }

        return response($message, Response::HTTP_LOCKED)
            ->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
