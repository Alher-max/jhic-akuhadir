<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureOtpIsVerified
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if ($user && in_array($user->role, ['student', 'member', 'parent'])) {
            if (!$user->hasVerifiedEmail()) {
                $user->email_verified_at = \Carbon\Carbon::now();
                $user->save();
            }
        } elseif ($user && !$user->hasVerifiedEmail()) {
            $email = $user->email;
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            session(['verify_email' => $email]);
            return redirect()->route('register.verify-otp')->with('error', 'Silakan verifikasi OTP Anda terlebih dahulu sebelum mengakses sistem.');
        }

        return $next($request);
    }
}
