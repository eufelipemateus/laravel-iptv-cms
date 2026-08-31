<?php

namespace App\Http\Middleware;

use App\Models\IPTVConfig;
use App\Services\OperationModeService;
use Closure;
use Illuminate\Http\Request;

class PublicCdnMiddleware
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->operationModeService->isM3u8()) {
            abort(404);
        }

        if (! IPTVConfig::get('URL_CDN')) {
            return response(['Url cdn is disabled.'], 503);
        }

        return $next($request);
    }
}
