<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class OwnerDashboardController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenant_id;
        
        $totalMembers = User::where('tenant_id', $tenantId)->count();
        $superAdmins = User::where('tenant_id', $tenantId)->where('role', 'super_admin')->get();
        
        $hasActiveSuperAdmin = $superAdmins->where('is_active', true)->isNotEmpty();
        
        $pendingInvitations = \App\Models\Invitation::where('tenant_id', $tenantId)
            ->where('role', 'super_admin')
            ->where('status', 'pending')
            ->get();

        return view('owner.dashboard', compact('totalMembers', 'superAdmins', 'hasActiveSuperAdmin', 'pendingInvitations'));
    }

    public function inviteSuperAdmin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        \App\Models\Invitation::create([
            'tenant_id' => Auth::user()->tenant_id,
            'email' => $request->email,
            'role' => 'super_admin',
            'token' => \Illuminate\Support\Str::random(40),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Undangan Super Admin berhasil dibuat. Silakan bagikan tautan kepada yang bersangkutan.');
    }

    public function toggleSuperAdmin($id)
    {
        $admin = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'super_admin')
            ->findOrFail($id);

        $admin->is_active = !$admin->is_active;
        $admin->save();

        return redirect()->back()->with('success', 'Status Super Admin berhasil diubah.');
    }
}
