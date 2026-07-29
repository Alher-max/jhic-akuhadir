<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SetTenantTimezone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timezone = 'Asia/Jakarta';

        if (Auth::check()) {
            $user = Auth::user();
            $tenant = $user->tenant ?? null;

            if ($tenant && !empty($tenant->timezone)) {
                $timezone = $tenant->timezone;
            }
        }

        date_default_timezone_set($timezone);
        config(['app.timezone' => $timezone]);

        return $next($request);
    }
}
