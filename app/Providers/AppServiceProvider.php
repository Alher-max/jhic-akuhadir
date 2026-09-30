<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Master Bypass for Kepala Sekolah / Owner
        Gate::before(function ($user, $ability) {
            if (in_array($user->role, ['headmaster', 'kepala_sekolah', 'owner'])) {
                return true;
            }
        });

        Gate::define('headmaster', fn ($user) => in_array($user->role, ['headmaster', 'kepala_sekolah', 'owner']));
        Gate::define('operator', fn ($user) => in_array($user->role, ['operator', 'admin_dapodik', 'admin']));
        Gate::define('teacher', fn ($user) => in_array($user->role, ['teacher', 'guru', 'wali_kelas', 'manager_teacher']));
        Gate::define('student', fn ($user) => in_array($user->role, ['student', 'member']));
        Gate::define('parent', fn ($user) => $user->role === 'parent');

        // Aliases for compatibility
        Gate::define('kepala_sekolah', fn ($user) => in_array($user->role, ['headmaster', 'kepala_sekolah', 'owner']));
        Gate::define('admin_dapodik', fn ($user) => in_array($user->role, ['operator', 'admin_dapodik', 'admin']));
        Gate::define('wali_kelas', fn ($user) => in_array($user->role, ['teacher', 'guru', 'wali_kelas']));

        // Combined role checks
        Gate::define('manage_schedules', fn ($user) => in_array($user->role, ['headmaster', 'kepala_sekolah', 'operator', 'admin_dapodik', 'teacher', 'wali_kelas']));

        // Smart Rate Limiting for API & Presensi (60 req/min per IP with Demo Exceptions)
        $smartLimiter = function (Request $request) {
            // Pengecualian 1: Rute demo web dan rute unduh APK
            if (
                $request->is('demo-login') ||
                $request->is('download/apk/*') ||
                $request->routeIs('demo.*', 'apk.*')
            ) {
                return Limit::none();
            }

            // Pengecualian 2: Pengguna yang sedang login adalah akun demo
            $user = $request->user();
            $demoAccounts = config('demo.accounts', []);
            if ($user && $this->isDemoEmail($user->email, $demoAccounts)) {
                return Limit::none();
            }

            // Pengecualian 3: Permintaan yang menyertakan kredensial demo (email)
            $email = Str::lower(trim((string) ($request->input('email') ?? $request->input('login_id') ?? '')));
            if ($email && $this->isDemoEmail($email, $demoAccounts)) {
                return Limit::none();
            }

            return Limit::perMinute(60)->by($request->ip());
        };

        RateLimiter::for('api', $smartLimiter);
        RateLimiter::for('attendance', $smartLimiter);
    }

    /**
     * Check if an email belongs to a demo account.
     */
    private function isDemoEmail(?string $email, array $demoAccounts): bool
    {
        if (! $email) {
            return false;
        }

        $lowerEmail = Str::lower(trim($email));

        return array_key_exists($lowerEmail, $demoAccounts)
            || (str_ends_with($lowerEmail, '@hadiryuk.id') && str_contains($lowerEmail, '.demo@'));
    }
}
