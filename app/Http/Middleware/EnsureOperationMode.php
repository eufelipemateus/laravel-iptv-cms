<?php

namespace App\Http\Middleware;

use App\Enums\OperationMode;
use App\Services\OperationModeService;
use Closure;
use Illuminate\Http\Request;

class EnsureOperationMode
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    public function handle(Request $request, Closure $next, ?string $mode = null)
    {
        $expectedMode = $this->resolveExpectedMode($request, $mode);

        if ($expectedMode !== null && ! $this->operationModeService->is($expectedMode)) {
            abort(404);
        }

        return $next($request);
    }

    private function resolveExpectedMode(Request $request, ?string $mode): ?OperationMode
    {
        if ($mode !== null) {
            $explicitMode = OperationMode::tryFrom($mode);

            if ($explicitMode === null) {
                abort(404);
            }

            return $explicitMode;
        }

        if ($request->is('public/m3u8/*') || $request->is('client/m3u8/*')) {
            return OperationMode::M3U8;
        }

        if ($request->is('api/v1/tv/*') || $request->is('tv3/*')) {
            return OperationMode::DTV3;
        }

        return null;
    }
}
