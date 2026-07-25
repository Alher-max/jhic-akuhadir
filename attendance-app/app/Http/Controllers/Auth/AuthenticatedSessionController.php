<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        if (!Auth::user()->is_active) {
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan.');
        }

        if (!Auth::user()->hasVerifiedEmail()) {
            $user = Auth::user();
            
            $otpCode = sprintf("%06d", mt_rand(1, 999999));
            $user->otp_code = $otpCode;
            $user->otp_expires_at = \Carbon\Carbon::now()->addMinutes(15);
            $user->save();
            
            app(\App\Services\OtpService::class)->sendOtp($user, $otpCode);
            
            $email = $user->email;
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            
            session(['verify_email' => $email]);
            return redirect()->route('register.verify-otp')->with('error', 'Silakan verifikasi OTP Anda terlebih dahulu. Kode OTP baru telah dikirim ke email Anda.');
        }

        $request->session()->regenerate();

        $role = Auth::user()->role;
        
        if (in_array($role, ['headmaster', 'kepala_sekolah'])) {
            return redirect()->intended(route('headmaster.dashboard', absolute: false));
        } elseif (in_array($role, ['student', 'member'])) {
            return redirect()->intended(route('student.dashboard', absolute: false));
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
