<?php

namespace  App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\IPTVConfig;
use App\Services\OperationModeService;

class PublicCdnMiddleware
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $this->operationModeService->isM3u8()) {
            abort(404);
        }

        if(!IPTVConfig::get('URL_CDN')){
            return response(['Url cdn is disabled.'], 503);
        }
        return  $next($request);
    }
}
