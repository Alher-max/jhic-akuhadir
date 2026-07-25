<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TenantOnboardingMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user && $user->tenant) {
            $tenant = $user->tenant;

            if (!$tenant->onboarding_completed) {
                // If user is admin_dapodik, they MUST complete onboarding
                if ($user->role === 'admin_dapodik') {
                    return redirect()->route('admin.onboarding');
                }

                // If user is kepala_sekolah
                if ($user->role === 'kepala_sekolah') {
                    $hasAdmin = \App\Models\User::where('tenant_id', $user->tenant_id)
                        ->where('role', 'admin_dapodik')
                        ->exists();
                    
                    // Mode Mandiri: 0 admin_dapodik, kepala_sekolah must complete onboarding
                    if (!$hasAdmin) {
                        return redirect()->route('admin.onboarding');
                    }
                    
                    // If owner has super_admins, let them bypass onboarding
                    // and allow them to reach dashboard.
                }
            }
        }

        return $next($request);
    }
}
