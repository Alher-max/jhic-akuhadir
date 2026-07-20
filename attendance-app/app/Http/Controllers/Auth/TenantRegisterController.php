<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\OtpMail;
use Carbon\Carbon;

class TenantRegisterController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'institution_type' => ['required', 'string', 'max:255'],
            'tenant_name' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $code = Str::upper(Str::random(6));
        while (Tenant::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(6));
        }

        $otpCode = sprintf("%06d", mt_rand(1, 999999));

        $user = DB::transaction(function () use ($request, $code, $otpCode) {
            $tenant = Tenant::create([
                'name' => $request->tenant_name,
                'institution_type' => $request->institution_type,
                'slug' => Str::slug($request->tenant_name) . '-' . strtolower($code),
                'code' => $code,
            ]);

            return User::create([
                'tenant_id' => $tenant->id,
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'owner',
                'otp_code' => $otpCode,
                'otp_expires_at' => Carbon::now()->addMinutes(15)
            ]);
        });

        // Send OTP via Email (will be logged if Mail is configured to log)
        Mail::to($user->email)->send(new OtpMail($otpCode));

        // Store email in session for verification page
        session(['verify_email' => $user->email]);

        return redirect()->route('register.verify-otp');
    }
}
