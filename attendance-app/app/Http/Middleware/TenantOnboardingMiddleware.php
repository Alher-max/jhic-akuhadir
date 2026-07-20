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
                // If user is super_admin, they MUST complete onboarding
                if ($user->role === 'super_admin') {
                    return redirect()->route('admin.onboarding');
                }

                // If user is owner
                if ($user->role === 'owner') {
                    $superAdminsCount = \App\Models\User::where('tenant_id', $tenant->id)
                        ->where('role', 'super_admin')
                        ->count();

                    // Mode Mandiri: 0 super_admins, owner must complete onboarding
                    if ($superAdminsCount === 0) {
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
