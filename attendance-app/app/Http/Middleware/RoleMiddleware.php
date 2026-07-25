<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect('login');
        }

        $user = auth()->user();

        if (!$user->is_active) {
            auth()->logout();
            return redirect()->route('login')->with('error', 'Akun Anda sedang dinonaktifkan.');
        }

        // Map standardized roles and aliases
        $expandedRoles = [];
        foreach ($roles as $role) {
            $expandedRoles[] = $role;
            if ($role === 'headmaster') {
                $expandedRoles[] = 'kepala_sekolah';
                $expandedRoles[] = 'owner';
            } elseif ($role === 'operator') {
                $expandedRoles[] = 'admin_dapodik';
                $expandedRoles[] = 'admin';
            } elseif ($role === 'teacher') {
                $expandedRoles[] = 'guru';
                $expandedRoles[] = 'wali_kelas';
                $expandedRoles[] = 'manager_teacher';
            } elseif ($role === 'student') {
                $expandedRoles[] = 'member';
            }
        }

        if (!empty($roles) && !in_array($user->role, $expandedRoles)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk halaman ini.');
        }

        return $next($request);
    }
}
