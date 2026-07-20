<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::view('/offline', 'errors.offline');

Route::middleware(['auth', 'verified'])->group(function () {
    // Owner Routes
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':owner'])->group(function () {
        Route::get('/owner/dashboard', [\App\Http\Controllers\OwnerDashboardController::class, 'index'])->name('owner.dashboard');
        Route::post('/owner/super-admin', [\App\Http\Controllers\OwnerDashboardController::class, 'inviteSuperAdmin'])->name('owner.super-admin.store');
        Route::patch('/owner/super-admin/{id}/toggle', [\App\Http\Controllers\OwnerDashboardController::class, 'toggleSuperAdmin'])->name('owner.super-admin.toggle');
    });

    // Admin Onboarding Wizard (For owner & super_admin)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':owner,super_admin'])->group(function () {
        Route::get('/admin/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'index'])->name('admin.onboarding');
        Route::post('/admin/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'finish']);
    });

    // Admin & Manager Routes (Operational)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':owner,super_admin,manager_teacher', 'tenant.onboarding'])->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('dashboard');
        
        // Management Routes (Schedules)
        Route::resource('dashboard/schedules', \App\Http\Controllers\Admin\ScheduleController::class)->except(['show']);
    });

    // Manager & Owner Routes (Leave Management & Reports)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':owner,manager_teacher', 'tenant.onboarding'])->group(function () {
        Route::get('/dashboard/leaves', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'index'])->name('admin.leaves.index');
        Route::patch('/dashboard/leaves/{id}/approve', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'approve'])->name('admin.leaves.approve');
        Route::patch('/dashboard/leaves/{id}/reject', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'reject'])->name('admin.leaves.reject');
        Route::get('/dashboard/leaves/{id}/download', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'download'])->name('admin.leaves.download');
        
        Route::get('/dashboard/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/dashboard/reports/export-excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->name('admin.reports.export-excel');
        Route::get('/dashboard/reports/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('admin.reports.export-pdf');
    });

    // Member Routes
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':staff_student'])->group(function () {
        Route::get('/member/dashboard', [\App\Http\Controllers\MemberDashboardController::class, 'index'])->name('member.dashboard');
        Route::post('/member/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('member.clock-in');
        Route::post('/member/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('member.clock-out');
        
        Route::get('/member/leaves/create', [\App\Http\Controllers\LeaveRequestController::class, 'create'])->name('member.leaves.create');
        Route::post('/member/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('member.leaves.store');
    });

    // Common Auth Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'store']);
});

Route::middleware('guest')->group(function () {
    Route::get('/register/super-admin/{token}', [\App\Http\Controllers\Auth\SuperAdminRegistrationController::class, 'create'])
        ->name('register.super-admin');
    Route::post('/register/super-admin/{token}', [\App\Http\Controllers\Auth\SuperAdminRegistrationController::class, 'store']);

    Route::get('/register/staff/{token}', [\App\Http\Controllers\Auth\StaffRegistrationController::class, 'create'])
        ->name('register.staff');
    Route::post('/register/staff/{token}', [\App\Http\Controllers\Auth\StaffRegistrationController::class, 'store']);

    Route::get('/register/member', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'create'])
        ->name('register.member');
    Route::post('/register/member', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'store']);

    // API Routes for Tenant Search (Accessible by guests for registration)
    Route::get('/api/tenants/search', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'searchTenants']);
    Route::get('/api/tenants/check-code', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'checkCode']);
});

require __DIR__.'/auth.php';
