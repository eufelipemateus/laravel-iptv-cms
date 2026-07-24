<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CustomerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $username = $request->getUser() ?: $request->query('user');
        $password = $request->getPassword() ?: $request->query('pass');
        $has_supplied_credentials = filled($username) && filled($password);

        if ($has_supplied_credentials) {
            $customer = Customer::where('username', $username)
                ->where('hash_acess', $password)
                ->first();

            $request->attributes->set('customer', $customer);
        }

        $is_not_authenticated = (
            ! $has_supplied_credentials ||
            ! isset($customer)
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
