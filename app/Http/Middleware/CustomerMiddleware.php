<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use App\Services\OperationModeService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    public function __construct(private readonly OperationModeService $operationModeService)
    {
    }

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (! $this->operationModeService->isM3u8()) {
            abort(404);
        }

        $tokenId = $request->getUser();
        $tokenSecret = $request->getPassword();
        $has_supplied_credentials = filled($tokenId) && filled($tokenSecret);
        $customer = null;

        if ($has_supplied_credentials) {
            $candidate = Customer::where('auth_token_id', $tokenId)->first();

            if ($candidate instanceof Customer && $candidate->canUseAuthToken((string) $tokenSecret)) {
                $candidate->markAuthTokenUsed();
                $customer = $candidate;
            }

            $request->attributes->set('customer', $customer);
        }

        $is_not_authenticated = (
            ! $has_supplied_credentials ||
            ! $customer instanceof Customer
        );

        if ($is_not_authenticated) {
            return response('This operation is unauthorized!', Response::HTTP_UNAUTHORIZED)
                ->header('Cache-Control', 'no-cache, must-revalidate, max-age=0')
                ->header('WWW-Authenticate', 'Basic realm="Access denied"');
        }

        if (! $customer->active) {
            return response('This Customer is not Active!', Response::HTTP_UNAUTHORIZED)
                ->header('Cache-Control', 'no-cache, must-revalidate, max-age=0');
        }

        if ($customer->defeated) {
            return response('This Customer is defeated!', Response::HTTP_UNAUTHORIZED)
                ->header('Cache-Control', 'no-cache, must-revalidate, max-age=0');
        }

        return $next($request);
    }
}
