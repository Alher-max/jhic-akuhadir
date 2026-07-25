<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\RateLimiter;

class OtpVerificationController extends Controller
{
    public function show()
    {
        if (!session()->has('verify_email')) {
            return redirect()->route('register');
        }

        $localOtp = null;
        if (app()->environment('local')) {
            $user = User::where('email', session('verify_email'))->first();
            $localOtp = $user ? $user->otp_code : null;
        }

        return view('auth.verify-otp', compact('localOtp'));
    }

    public function verify(Request $request)
    {
        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        $email = session('verify_email');
        if (!$email) {
            return redirect()->route('register');
        }

        $user = User::where('email', $email)->first();

        if (!$user) {
            return redirect()->route('register');
        }

        $key = 'verify-otp:'.$email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            if ($user->otp_code !== null) {
                $user->otp_code = null;
                $user->otp_expires_at = null;
                $user->save();
            }
            return back()->withErrors(['otp' => 'Anda telah salah memasukkan OTP sebanyak 5 kali. Kode ini telah dihanguskan untuk keamanan. Silakan minta ulang OTP baru.']);
        }

        if (empty($user->otp_code)) {
            return back()->withErrors(['otp' => 'Tidak ada kode OTP aktif atau kode telah dihanguskan. Silakan minta ulang OTP baru.']);
        }

        if ($user->otp_code !== $request->otp && !(app()->environment('local') && $request->otp === '123456')) {
            RateLimiter::hit($key, 3600);
            $attemptsLeft = 5 - RateLimiter::attempts($key);
            return back()->withErrors(['otp' => 'Kode OTP salah. (Sisa percobaan: '.$attemptsLeft.')']);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan minta ulang.']);
        }

        // OTP is valid
        RateLimiter::clear($key);
        RateLimiter::clear('resend-otp:'.$email);

        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->is_active = true;
        if (empty($user->role)) {
            $user->role = 'student';
        }
        $user->save();

        session()->forget('verify_email');

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function resend(Request $request)
    {
        $email = session('verify_email');
        if (!$email) {
            return response()->json(['message' => 'Sesi tidak ditemukan'], 400);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json(['message' => 'Pengguna tidak ditemukan'], 404);
        }

        $key = 'resend-otp:'.$email;

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return response()->json(['message' => 'Terlalu banyak percobaan kirim ulang OTP. Coba lagi dalam ' . ceil($seconds / 60) . ' menit.'], 429);
        }

        // Generate new OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(5);
        $user->save();

        app(\App\Services\OtpService::class)->sendOtp($user, $otpCode);

        RateLimiter::hit($key, 3600);

        return response()->json(['message' => 'Kode OTP baru telah dikirim.']);
    }
}
