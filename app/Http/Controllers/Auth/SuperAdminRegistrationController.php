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

class SuperAdminRegistrationController extends Controller
{
    public function create($token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->where('role', 'admin_dapodik')
            ->first();

        if (!$invitation) {
            return redirect()->route('login')->with('error', 'Tautan undangan tidak valid atau sudah kedaluwarsa.');
        }

        $tenant = Tenant::findOrFail($invitation->tenant_id);

        return view('auth.register-super-admin', compact('invitation', 'tenant'));
    }

    public function store(Request $request, $token)
    {
        $invitation = Invitation::where('token', $token)
            ->where('status', 'pending')
            ->where('role', 'admin_dapodik')
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
            'role' => 'admin_dapodik',
            'onboarding_completed' => true,
        ]);

        $invitation->update(['status' => 'accepted']);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
