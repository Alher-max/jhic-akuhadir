<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

use Illuminate\Support\Facades\Gate;

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
    }
}
