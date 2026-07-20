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
        // Master Bypass for Owner
        Gate::before(function ($user, $ability) {
            if ($user->role === 'owner') {
                return true;
            }
        });

        Gate::define('owner', fn ($user) => $user->role === 'owner');
        Gate::define('super_admin', fn ($user) => $user->role === 'super_admin');
        Gate::define('manager_teacher', fn ($user) => $user->role === 'manager_teacher');
        Gate::define('staff_student', fn ($user) => $user->role === 'staff_student');

        // Combined role checks
        Gate::define('manage_schedules', fn ($user) => in_array($user->role, ['owner', 'super_admin', 'manager_teacher']));
    }
}
