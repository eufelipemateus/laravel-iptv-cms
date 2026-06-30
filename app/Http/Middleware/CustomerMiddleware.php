<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;

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
        $AUTH_USER = 'admin';
        $AUTH_PASS = 'admin';
        header('Cache-Control: no-cache, must-revalidate, max-age=0');

        $has_supplied_credentials = ! (
            empty($_SERVER['PHP_AUTH_USER']) &&
            empty($_SERVER['PHP_AUTH_PW'])
        );

        if ($has_supplied_credentials) {
            $customer = Customer::where('username', $_SERVER['PHP_AUTH_USER'])
                ->where('hash_acess', $_SERVER['PHP_AUTH_PW'])
                ->first();

            $request->attributes->set('customer', $customer);
            $request->attributes->set('custormer', $customer);
        }

        $is_not_authenticated = (
            ! $has_supplied_credentials ||
            ! isset($customer)
        );

        if ($is_not_authenticated) {
            header('HTTP/1.1 401 Authorization Required');
            header('WWW-Authenticate: Basic realm="Access denied"');
            echo 'This operation is unthorizated!';
            exit();
        }

        if (! $customer->active) {
            header('HTTP/1.1 401 CUSTOMER INACTIVE');
            echo 'This Customer is not Active!';
            exit();
        }

        if ($customer->defeated) {
            header('HTTP/1.1 401 CUSTOMER INVOCE DEFEATED');
            echo 'This Customer is defeated!';
            exit();
        }

        return $next($request);
    }
}
