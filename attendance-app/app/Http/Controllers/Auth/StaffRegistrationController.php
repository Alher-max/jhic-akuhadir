<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class StaffRegistrationController extends Controller
{
    public function create($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'Tautan undangan tidak valid atau sudah kedaluwarsa.');
        }

        $tenant = Tenant::findOrFail($invitation->tenant_id);

        return view('auth.register-staff', compact('invitation', 'tenant'));
    }

    public function store(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->firstOrFail();

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'tenant_id' => $invitation->tenant_id,
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role' => $invitation->role ?: 'operator',
            'is_active' => true,
            'onboarding_completed' => true, // Staf tidak perlu onboarding
        ]);

        $invitation->update(['status' => 'accepted']);

        event(new Registered($user));

        $otpCode = sprintf("%06d", mt_rand(1, 999999));
        $user->otp_code = $otpCode;
        $user->otp_expires_at = \Carbon\Carbon::now()->addMinutes(15);
        $user->save();

        app(\App\Services\OtpService::class)->sendOtp($user, $otpCode);

        session(['verify_email' => $user->email]);

        return redirect()->route('register.verify-otp');
    }
}
