<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->must_change_password) {
            // Biarkan lewat jika rutenya adalah change-password atau logout
            $allowedRoutes = [
                'password.change',
                'password.change.store',
                'logout',
            ];

            if (!$request->routeIs($allowedRoutes)) {
                return redirect()->route('password.change')->with('warning', 'Demi keamanan, Anda diwajibkan untuk mengganti kata sandi default sebelum melanjutkan.');
            }
        }

        return $next($request);
    }
}
