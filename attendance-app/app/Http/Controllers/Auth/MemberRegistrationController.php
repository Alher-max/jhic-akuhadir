<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class MemberRegistrationController extends Controller
{
    /**
     * Search tenants by name (Live Search)
     */
    public function searchTenants(Request $request)
    {
        $query = $request->query('q');

        if (!$query || strlen($query) < 3) {
            return response()->json([]);
        }

        $tenants = Tenant::where('name', 'LIKE', '%' . $query . '%')
            ->select('id', 'name', 'code')
            ->limit(10)
            ->get();

        return response()->json($tenants);
    }

    /**
     * Check tenant by exact 6-digit code
     */
    public function checkCode(Request $request)
    {
        $code = $request->query('code');

        if (!$code) {
            return response()->json(null);
        }

        $tenant = Tenant::where('code', strtoupper($code))
            ->select('id', 'name', 'code')
            ->first();

        return response()->json($tenant);
    }

    /**
     * Show member registration view
     */
    public function create()
    {
        return view('auth.register-member');
    }

    /**
     * Handle member registration
     */
    public function store(Request $request)
    {
        $request->validate([
            'tenant_id' => ['required', 'exists:tenants,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ], [
            'tenant_id.required' => 'Silakan pilih atau masukkan kode institusi yang valid terlebih dahulu.',
            'tenant_id.exists' => 'Institusi yang dipilih tidak valid.'
        ]);

        $user = User::create([
            'tenant_id' => $request->tenant_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'onboarding_completed' => true, // Bypass onboarding for members
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
