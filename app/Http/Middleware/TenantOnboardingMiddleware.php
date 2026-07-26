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
            // Bypass onboarding completely for non-headmaster roles (student, teacher, operator, parent)
            if (!in_array($user->role, ['headmaster', 'kepala_sekolah', 'owner'])) {
                return $next($request);
            }

            $tenant = $user->tenant;

            if (!$tenant->onboarding_completed) {
                $hasAdmin = \App\Models\User::where('tenant_id', $user->tenant_id)
                    ->whereIn('role', ['operator', 'admin_dapodik', 'admin'])
                    ->exists();
                
                // Mode Mandiri: 0 operator/admin_dapodik, headmaster must complete onboarding
                if (!$hasAdmin) {
                    return redirect()->route('admin.onboarding');
                }
            }
        }

        return $next($request);
    }
}
