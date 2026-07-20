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

class OtpVerificationController extends Controller
{
    public function show()
    {
        if (!session()->has('verify_email')) {
            return redirect()->route('register');
        }

        return view('auth.verify-otp');
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

        if ($user->otp_code !== $request->otp) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        if (Carbon::now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'Kode OTP telah kedaluwarsa. Silakan minta ulang.']);
        }

        // OTP is valid
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = Carbon::now();
        $user->save();

        session()->forget('verify_email');

        event(new Registered($user));
        Auth::login($user);

        return redirect()->route('onboarding');
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

        // Generate new OTP
        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        $user->otp_code = $otpCode;
        $user->otp_expires_at = Carbon::now()->addMinutes(15);
        $user->save();

        Mail::to($user->email)->send(new OtpMail($otpCode));

        return response()->json(['message' => 'Kode OTP baru telah dikirim.']);
    }
}
