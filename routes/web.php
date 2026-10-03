<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeroomAnnouncementController;
use App\Http\Controllers\DemoLoginController;
use App\Http\Controllers\ApkDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/demo-login', [DemoLoginController::class, 'store'])
    ->name('demo.login');

Route::get('/download/apk/{role}', ApkDownloadController::class)
    ->name('apk.download');

Route::view('/offline', 'errors.offline');

// Redirects for backward compatibility
Route::redirect('/owner/dashboard', '/headmaster/dashboard', 301);
Route::redirect('/owner', '/headmaster/dashboard', 301);
Route::redirect('/kepsek/dashboard', '/headmaster/dashboard', 301);
Route::redirect('/kepsek', '/headmaster/dashboard', 301);

// Subdomain Routes (Tenant Landing Pages)
Route::domain('{subdomain}.'.config('app.domain', 'localhost'))->middleware(['tenant.subdomain'])->group(function () {
    Route::get('/', function () {
        return view('tenant.landing', ['tenant' => app('tenant')]);
    })->name('tenant.landing');
});

Route::middleware(['auth', 'otp.verified'])->group(function () {
    // Push Subscription
    Route::post('/push-subscription', [\App\Http\Controllers\PushSubscriptionController::class, 'store'])->name('push-subscription.store');
    Route::delete('/push-subscription', [\App\Http\Controllers\PushSubscriptionController::class, 'destroy'])->name('push-subscription.destroy');

    // Headmaster Routes
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':headmaster'])->group(function () {
        Route::get('/headmaster/dashboard', [\App\Http\Controllers\OwnerDashboardController::class, 'index'])->name('headmaster.dashboard');
        Route::get('/headmaster/api/search-student', [\App\Http\Controllers\OwnerDashboardController::class, 'searchStudent'])->name('headmaster.api.search-student');
        Route::get('/headmaster/api/search-teacher', [\App\Http\Controllers\OwnerDashboardController::class, 'searchTeacher'])->name('headmaster.api.search-teacher');
        Route::post('/headmaster/super-admin', [\App\Http\Controllers\OwnerDashboardController::class, 'inviteSuperAdmin'])->name('headmaster.super-admin.store');
        Route::patch('/headmaster/super-admin/{id}/toggle', [\App\Http\Controllers\OwnerDashboardController::class, 'toggleSuperAdmin'])->name('headmaster.super-admin.toggle');
    });

    // Admin Onboarding Wizard (For headmaster & operator)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':headmaster,operator'])->group(function () {
        Route::get('/admin/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'index'])->name('admin.onboarding');
        Route::post('/admin/onboarding', [\App\Http\Controllers\Admin\OnboardingController::class, 'finish']);
    });

    // Dashboard Smart Dispatcher
    Route::get('/dashboard', function () {
        $role = auth()->user()->role;
        $homeroomClasses = \App\Models\SchoolClass::where('tenant_id', auth()->user()->tenant_id ?? 0)
            ->where('wali_kelas_id', auth()->id())
            ->get();
        $isHomeroom = $homeroomClasses->isNotEmpty() || $role === 'wali_kelas';

        if (in_array($role, ['headmaster', 'kepala_sekolah', 'owner'])) {
            return redirect()->route('headmaster.dashboard');
        } elseif (in_array($role, ['operator', 'admin_dapodik', 'admin'])) {
            return redirect()->route('operator.dashboard');
        } elseif ($isHomeroom) {
            return redirect()->route('homeroom.dashboard');
        } elseif (in_array($role, ['teacher', 'guru', 'guru_mapel', 'manager_teacher'])) {
            return redirect()->route('teacher.dashboard');
        } elseif ($role === 'parent') {
            return redirect()->route('parent.dashboard');
        } else {
            return redirect()->route('student.dashboard');
        }
    })->name('dashboard');

    // Operator Dashboard Route (Strict Role: operator)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':operator', 'tenant.onboarding'])->group(function () {
        Route::get('/operator/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('operator.dashboard');
        Route::view('/operator/attendance/scanner', 'operator.scanner')->name('operator.attendance.scanner');
        Route::redirect('/manager/dashboard', '/operator/dashboard');
        Route::post('/operator/dashboard/banner', [\App\Http\Controllers\AdminDashboardController::class, 'updateBanner'])->name('operator.dashboard.banner');
    });

    // Teacher Dashboard Route (Strict Role: teacher)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':teacher,operator,headmaster', 'tenant.onboarding'])->group(function () {
        Route::get('/teacher/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::post('/teacher/manual-attendance', [\App\Http\Controllers\AdminDashboardController::class, 'storeManualAttendance'])->name('teacher.manual-attendance');
        Route::post('/teacher/kbm-attendance', [\App\Http\Controllers\AdminDashboardController::class, 'storeKbmAttendance'])->name('teacher.kbm-attendance');
        Route::post('/teacher/students/{id}/reset-photo', [\App\Http\Controllers\AdminDashboardController::class, 'resetStudentPhoto'])->name('teacher.students.reset-photo');

        // Modul Rapor: Buku Nilai Guru Mapel (Sprint 2)
        Route::get('/teacher/gradebook', [\App\Http\Controllers\Teacher\GradebookController::class, 'index'])->name('teacher.gradebook.index');
        Route::get('/teacher/gradebook/{class}/{subject}', [\App\Http\Controllers\Teacher\GradebookController::class, 'show'])->name('teacher.gradebook.show');
        Route::post('/teacher/gradebook', [\App\Http\Controllers\Teacher\GradebookController::class, 'store'])->name('teacher.gradebook.store');
        Route::get('/teacher/gradebook/{class}/{subject}/export-erapor', [\App\Http\Controllers\ReportExportController::class, 'exportERapor'])->name('teacher.gradebook.export-erapor');
        Route::get('/teacher/gradebook/{class}/{subject}/export-rdm', [\App\Http\Controllers\ReportExportController::class, 'exportRDM'])->name('teacher.gradebook.export-rdm');

        // Aliases for Gradebook
        Route::get('/gradebook', [\App\Http\Controllers\Teacher\GradebookController::class, 'index'])->name('gradebook.index');
        Route::get('/gradebook/{class}/{subject}', [\App\Http\Controllers\Teacher\GradebookController::class, 'show'])->name('gradebook.show');
        Route::post('/gradebook', [\App\Http\Controllers\Teacher\GradebookController::class, 'store'])->name('gradebook.store');
        Route::get('/gradebook/{class}/{subject}/export-erapor', [\App\Http\Controllers\ReportExportController::class, 'exportERapor'])->name('gradebook.export-erapor');
        Route::get('/gradebook/{class}/{subject}/export-rdm', [\App\Http\Controllers\ReportExportController::class, 'exportRDM'])->name('gradebook.export-rdm');

        // Modul Rapor: Tujuan Pembelajaran (TP)
        Route::get('/teacher/learning-objectives', [\App\Http\Controllers\Teacher\LearningObjectiveController::class, 'index'])->name('teacher.learning-objectives.index');
        Route::post('/teacher/learning-objectives', [\App\Http\Controllers\Teacher\LearningObjectiveController::class, 'store'])->name('teacher.learning-objectives.store');
        Route::delete('/teacher/learning-objectives/{learningObjective}', [\App\Http\Controllers\Teacher\LearningObjectiveController::class, 'destroy'])->name('teacher.learning-objectives.destroy');
    });

    // Homeroom Dashboard Route (Strict Role: homeroom teacher / wali kelas, with operator/headmaster supervision)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':teacher,operator,headmaster', 'tenant.onboarding'])->group(function () {
        Route::get('/homeroom/dashboard', [\App\Http\Controllers\AdminDashboardController::class, 'homeroomIndex'])->name('homeroom.dashboard');
        
        // Homeroom Announcements (Protected)
        Route::get('/teacher/announcements', [HomeroomAnnouncementController::class, 'index'])->name('teacher.announcements.index');
        Route::post('/teacher/announcements', [HomeroomAnnouncementController::class, 'store'])->name('teacher.announcements.store');
        Route::delete('/teacher/announcements/{announcement}', [HomeroomAnnouncementController::class, 'destroy'])->name('teacher.announcements.destroy');

        // Modul Rapor: Dasbor Wali Kelas, Leger, Presensi, & Ekstrakurikuler (Sprint 3)
        Route::get('/homeroom/reports', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'index'])->name('homeroom.reports.index');
        Route::get('/homeroom/reports/{class}/leger', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'leger'])->name('homeroom.reports.leger');
        Route::get('/homeroom/reports/{class}/attendance', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'attendance'])->name('homeroom.reports.attendance');
        Route::get('/homeroom/reports/{class}/notes', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'notes'])->name('homeroom.reports.notes');
        Route::get('/homeroom/reports/{class}/extracurricular', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'extracurricular'])->name('homeroom.reports.extracurricular');
        Route::post('/homeroom/reports/{class}/sync-attendance', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'syncAttendance'])->name('homeroom.reports.sync-attendance');
        Route::post('/homeroom/reports/{class}/batch-update', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'batchUpdate'])->name('homeroom.reports.batch-update');
        Route::post('/homeroom/reports/extracurricular', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'storeExtracurricular'])->name('homeroom.reports.extracurricular.store');
        Route::delete('/homeroom/reports/extracurricular/{extracurricularGrade}', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'destroyExtracurricular'])->name('homeroom.reports.extracurricular.destroy');

        // Modul Rapor: Publikasi & Cetak Resmi A4 (Sprint 4)
        Route::post('/homeroom/reports/{class}/publish', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'publishClassReports'])->name('homeroom.reports.publish');
        Route::post('/homeroom/reports/{class}/lock', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'lockClassReports'])->name('homeroom.reports.lock');
        Route::get('/homeroom/reports/{class}/print/{student}', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'printSingle'])->name('homeroom.reports.print.single');
        Route::get('/homeroom/reports/{class}/print-batch', [\App\Http\Controllers\Homeroom\HomeroomReportController::class, 'printBatch'])->name('homeroom.reports.print.batch');

        // Modul Rapor: Ekspor Leger Nilai & Rekap Dapodik (Sprint 5)
        Route::get('/homeroom/reports/{class}/export-leger', [\App\Http\Controllers\ReportExportController::class, 'exportLeger'])->name('homeroom.reports.export-leger');

        // Modul Kokurikuler: Projek Penguatan Profil Pelajar Pancasila (P5) & P5RA (Sprint 6)
        Route::get('/p5/projects', [\App\Http\Controllers\P5\P5ProjectController::class, 'indexAll'])->name('p5.projects.index.all');
        Route::get('/p5/classes/{class}/projects', [\App\Http\Controllers\P5\P5ProjectController::class, 'index'])->name('p5.projects.index');
        Route::get('/p5/classes/{class}/projects/create', [\App\Http\Controllers\P5\P5ProjectController::class, 'create'])->name('p5.projects.create');
        Route::post('/p5/classes/{class}/projects', [\App\Http\Controllers\P5\P5ProjectController::class, 'store'])->name('p5.projects.store');
        Route::get('/p5/projects/{project}', [\App\Http\Controllers\P5\P5ProjectController::class, 'show'])->name('p5.projects.show');
        Route::delete('/p5/projects/{project}', [\App\Http\Controllers\P5\P5ProjectController::class, 'destroy'])->name('p5.projects.destroy');

        Route::get('/p5/projects/{project}/matrix', [\App\Http\Controllers\P5\P5AssessmentController::class, 'matrix'])->name('p5.assessments.matrix');
        Route::post('/p5/projects/{project}/matrix', [\App\Http\Controllers\P5\P5AssessmentController::class, 'batchStore'])->name('p5.assessments.batch-store');

        Route::get('/p5/projects/{project}/print-batch', [\App\Http\Controllers\P5\P5ReportPrintController::class, 'printBatch'])->name('p5.reports.print-batch');

        // Modul Vokasi SMK: PKL, Presensi Industri, & UKK (Sprint 7)
        Route::get('/vocational/classes/{class}/internships', [\App\Http\Controllers\Vocational\InternshipController::class, 'index'])->name('vocational.internships.index');
        Route::get('/vocational/classes/{class}/internships/create', [\App\Http\Controllers\Vocational\InternshipController::class, 'create'])->name('vocational.internships.create');
        Route::post('/vocational/classes/{class}/internships', [\App\Http\Controllers\Vocational\InternshipController::class, 'store'])->name('vocational.internships.store');
        Route::delete('/vocational/internships/{placement}', [\App\Http\Controllers\Vocational\InternshipController::class, 'destroy'])->name('vocational.internships.destroy');

        Route::get('/vocational/internships/{placement}/assessment', [\App\Http\Controllers\Vocational\InternshipAssessmentController::class, 'show'])->name('vocational.internships.assessment.show');
        Route::post('/vocational/internships/{placement}/assessment', [\App\Http\Controllers\Vocational\InternshipAssessmentController::class, 'store'])->name('vocational.internships.assessment.store');

        Route::get('/vocational/classes/{class}/ukk', [\App\Http\Controllers\Vocational\VocationalCompetencyController::class, 'index'])->name('vocational.ukk.index');
        Route::post('/vocational/classes/{class}/ukk', [\App\Http\Controllers\Vocational\VocationalCompetencyController::class, 'batchStore'])->name('vocational.ukk.batch-store');
    });

    // Admin & Wali Kelas Routes (Operational Management)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':headmaster,operator,teacher', 'tenant.onboarding'])->group(function () {
        

        // Management Routes (Schedules)
        Route::resource('dashboard/schedules', \App\Http\Controllers\Admin\ScheduleController::class)->except(['show']);
        
        // Class Schedules (Jadwal Pelajaran KBM)
        Route::get('/dashboard/class-schedules', [\App\Http\Controllers\ClassScheduleController::class, 'index'])->name('class-schedules.index');
        Route::post('/dashboard/class-schedules', [\App\Http\Controllers\ClassScheduleController::class, 'storeSchedule'])->name('class-schedules.store');
        Route::delete('/dashboard/class-schedules/{id}', [\App\Http\Controllers\ClassScheduleController::class, 'destroySchedule'])->name('class-schedules.destroy');
        Route::post('/dashboard/subjects', [\App\Http\Controllers\ClassScheduleController::class, 'storeSubject'])->name('subjects.store');
        Route::put('/dashboard/subjects/{subject}', [\App\Http\Controllers\ClassScheduleController::class, 'updateSubject'])->name('subjects.update');
        Route::post('/dashboard/subjects/presets', [\App\Http\Controllers\ClassScheduleController::class, 'loadSubjectPresets'])->name('subjects.presets');
        Route::post('/dashboard/subjects/presets/kemenag', [\App\Http\Controllers\ClassScheduleController::class, 'loadSubjectPresetsKemenag'])->name('subjects.presets.kemenag');
        Route::delete('/dashboard/subjects/presets', [\App\Http\Controllers\ClassScheduleController::class, 'clearSubjectPresets'])->name('subjects.presets.clear');
        Route::post('/dashboard/activities', [\App\Http\Controllers\ClassScheduleController::class, 'storeActivity'])->name('activities.store');
        Route::post('/dashboard/activities/presets', [\App\Http\Controllers\ClassScheduleController::class, 'loadActivityPresets'])->name('activities.presets');
        Route::delete('/dashboard/activities/presets', [\App\Http\Controllers\ClassScheduleController::class, 'clearActivityPresets'])->name('activities.presets.clear');
        Route::post('/dashboard/activities/{activity}/members', [\App\Http\Controllers\ClassScheduleController::class, 'updateActivityMembers'])->name('activities.members.update');
        Route::post('/dashboard/activities/{activity}/members/add', [\App\Http\Controllers\ClassScheduleController::class, 'addActivityMember'])->name('activities.members.add');
        Route::delete('/dashboard/activities/{activity}/members/{student}', [\App\Http\Controllers\ClassScheduleController::class, 'removeActivityMember'])->name('activities.members.remove');
        Route::delete('/dashboard/activities/{id}', [\App\Http\Controllers\ClassScheduleController::class, 'destroyActivity'])->name('activities.destroy');

        // Attendance Schedules (Jam Operasional Presensi Harian)
        Route::get('/dashboard/attendance-schedules', [\App\Http\Controllers\AttendanceScheduleController::class, 'index'])->name('attendance-schedules.index');
        Route::post('/dashboard/attendance-schedules', [\App\Http\Controllers\AttendanceScheduleController::class, 'update'])->name('attendance-schedules.update');
        Route::get('/dashboard/attendances', [\App\Http\Controllers\PwaAttendanceController::class, 'index'])->name('attendances.index');
        
        // Student Management
        Route::get('/dashboard/students', [\App\Http\Controllers\StudentManagementController::class, 'index'])->name('students.index');
        Route::post('/dashboard/students', [\App\Http\Controllers\StudentManagementController::class, 'store'])->name('students.store');
        Route::put('/dashboard/students/{id}', [\App\Http\Controllers\StudentManagementController::class, 'update'])->name('students.update');
        Route::delete('/dashboard/students/{id}', [\App\Http\Controllers\StudentManagementController::class, 'destroy'])->name('students.destroy');
        Route::post('/dashboard/students/{id}/reset-password', [\App\Http\Controllers\StudentManagementController::class, 'resetPassword'])->name('students.reset-password');
        Route::post('/dashboard/students/import', [\App\Http\Controllers\StudentManagementController::class, 'import'])->name('students.import');
        Route::get('/dashboard/students/download-template', [\App\Http\Controllers\StudentManagementController::class, 'downloadTemplate'])->name('students.download-template');

        // Class Management
        Route::get('/operator/classes', [\App\Http\Controllers\ClassManagementController::class, 'index'])->name('operator.classes.index');
        Route::get('/operator/classes/{class}', [\App\Http\Controllers\ClassManagementController::class, 'show'])->name('operator.classes.show');
        Route::post('/operator/classes', [\App\Http\Controllers\ClassManagementController::class, 'store'])->name('operator.classes.store');
        Route::post('/operator/classes/import-students', [\App\Http\Controllers\ClassManagementController::class, 'importStudents'])->name('operator.classes.import-students');
        Route::put('/operator/classes/{class}', [\App\Http\Controllers\ClassManagementController::class, 'update'])->name('operator.classes.update');
        Route::delete('/operator/classes/{class}', [\App\Http\Controllers\ClassManagementController::class, 'destroy'])->name('operator.classes.destroy');
        Route::post('/operator/classes/{class}/remove-student/{student}', [\App\Http\Controllers\ClassManagementController::class, 'removeStudent'])->name('operator.classes.remove-student');

        // Teacher Management
        Route::post('dashboard/teachers/quick-add', [\App\Http\Controllers\TeacherManagementController::class, 'quickStore'])->name('teachers.quick-store');
        Route::get('/operator/teachers/download-template', [\App\Http\Controllers\TeacherManagementController::class, 'downloadTemplate'])->name('operator.teachers.download-template');
        Route::get('/operator/teachers/export-csv', [\App\Http\Controllers\TeacherManagementController::class, 'exportCsv'])->name('operator.teachers.export-csv');
        Route::get('/operator/teachers', [\App\Http\Controllers\TeacherManagementController::class, 'index'])->name('operator.teachers.index');
        Route::get('/operator/teachers/{teacher}', [\App\Http\Controllers\TeacherManagementController::class, 'show'])->name('operator.teachers.show');
        Route::post('/operator/teachers', [\App\Http\Controllers\TeacherManagementController::class, 'store'])->name('operator.teachers.store');
        Route::put('/operator/teachers/{teacher}', [\App\Http\Controllers\TeacherManagementController::class, 'update'])->name('operator.teachers.update');
        Route::delete('/operator/teachers/{teacher}', [\App\Http\Controllers\TeacherManagementController::class, 'destroy'])->name('operator.teachers.destroy');
        Route::post('/operator/teachers/{teacher}/reset-password', [\App\Http\Controllers\TeacherManagementController::class, 'resetPassword'])->name('operator.teachers.reset-password');
        
        // Parent Management
        Route::get('/operator/parents', [\App\Http\Controllers\OperatorParentController::class, 'index'])->name('operator.parents.index');
        Route::post('/operator/parents', [\App\Http\Controllers\OperatorParentController::class, 'store'])->name('operator.parents.store');
        Route::post('/operator/parents/{parent}/link-student', [\App\Http\Controllers\OperatorParentController::class, 'linkStudent'])->name('operator.parents.link-student');
        Route::delete('/operator/parents/{parent}/unlink-student/{student}', [\App\Http\Controllers\OperatorParentController::class, 'unlinkStudent'])->name('operator.parents.unlink-student');
        Route::post('/operator/parents/{parent}/reset-password', [\App\Http\Controllers\OperatorParentController::class, 'resetPassword'])->name('operator.parents.reset-password');
        Route::delete('/operator/parents/{parent}', [\App\Http\Controllers\OperatorParentController::class, 'destroy'])->name('operator.parents.destroy');
        
        // Attendance Settings
        Route::get('/dashboard/attendance-settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'index'])->name('attendance-settings.index');
        Route::put('/dashboard/attendance-settings', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'updateSettings'])->name('attendance-settings.update');
        Route::put('/dashboard/attendance-settings/timezone', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'updateTimezone'])->name('attendance-settings.update-timezone');
        Route::post('/dashboard/attendance-settings/devices', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'storeDevice'])->name('attendance-settings.devices.store');
        Route::delete('/dashboard/attendance-settings/devices/{id}', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'destroyDevice'])->name('attendance-settings.devices.destroy');
        Route::post('/dashboard/attendance-settings/claim-device', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'claimDevice'])->name('attendance-settings.devices.claim');
        Route::post('/dashboard/attendance-settings/generate-secret-key', [\App\Http\Controllers\Admin\AttendanceSettingController::class, 'generateSecretKey'])->name('attendance-settings.generate-secret-key');

        // Student Cards Management (Kartu Pelajar)
        Route::get('/dashboard/student-cards', [\App\Http\Controllers\Admin\StudentCardController::class, 'index'])->name('student-cards.index');
        Route::post('/dashboard/student-cards/print', [\App\Http\Controllers\Admin\StudentCardController::class, 'printCards'])->name('student-cards.print');
        Route::post('/dashboard/student-cards/upload-logo', [\App\Http\Controllers\Admin\StudentCardController::class, 'uploadLogo'])->name('student-cards.upload-logo');
        Route::delete('/dashboard/student-cards/remove-logo', [\App\Http\Controllers\Admin\StudentCardController::class, 'removeLogo'])->name('student-cards.remove-logo');

        // Support Tickets Management (Operator)
        Route::get('/operator/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'operatorIndex'])->name('operator.support-tickets.index');
        Route::get('/operator/support-tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'operatorShow'])->name('operator.support-tickets.show');
        Route::put('/operator/support-tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'operatorUpdate'])->name('operator.support-tickets.update');
    });

    // Master Data Rapor: Tahun Ajaran & Semester (Operator & Kepala Sekolah)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':headmaster,operator', 'tenant.onboarding'])->group(function () {
        Route::patch('/admin/academic-years/{academic_year}/activate', [\App\Http\Controllers\Admin\AcademicYearController::class, 'activate'])
            ->name('admin.academic-years.activate');
        Route::resource('/admin/academic-years', \App\Http\Controllers\Admin\AcademicYearController::class, [
            'as' => 'admin',
        ]);

        // Aliases for convenience without 'admin.' prefix
        Route::patch('/academic-years/{academic_year}/activate', [\App\Http\Controllers\Admin\AcademicYearController::class, 'activate'])
            ->name('academic-years.activate');
        Route::resource('/academic-years', \App\Http\Controllers\Admin\AcademicYearController::class);
    });

    // Wali Kelas & Kepala Sekolah Routes (Leave Management & Reports)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':headmaster,teacher', 'tenant.onboarding'])->group(function () {
        Route::get('/dashboard/leaves', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'index'])->name('admin.leaves.index');
        Route::patch('/dashboard/leaves/{id}/approve', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'approve'])->name('admin.leaves.approve');
        Route::patch('/dashboard/leaves/{id}/reject', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'reject'])->name('admin.leaves.reject');
        Route::get('/dashboard/leaves/{id}/download', [\App\Http\Controllers\Admin\AdminLeaveController::class, 'download'])->name('admin.leaves.download');
    });
    
    // Laporan Kehadiran Routes (Bisa diakses oleh Admin, Operator, admin_dapodik, Kepala Sekolah, Wali Kelas)
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':operator,headmaster,teacher', 'tenant.onboarding'])->group(function () {
        Route::get('/dashboard/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
        Route::get('/dashboard/reports/export-excel', [\App\Http\Controllers\Admin\ReportController::class, 'exportExcel'])->name('admin.reports.export-excel');
        Route::get('/dashboard/reports/export-pdf', [\App\Http\Controllers\Admin\ReportController::class, 'exportPdf'])->name('admin.reports.export-pdf');
    });

    // Student Routes
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':student'])->group(function () {
        Route::get('/student/dashboard', [\App\Http\Controllers\MemberDashboardController::class, 'index'])->name('student.dashboard');
        Route::get('/member/dashboard', [\App\Http\Controllers\MemberDashboardController::class, 'index'])->name('member.dashboard');
        
        // PWA Routes
        Route::get('/pwa/clock-in', [\App\Http\Controllers\PwaAttendanceController::class, 'index'])->name('pwa.clock-in');
        Route::post('/pwa/clock-in', [\App\Http\Controllers\PwaAttendanceController::class, 'store'])->name('pwa.store');

        Route::post('/member/clock-in', [\App\Http\Controllers\AttendanceController::class, 'clockIn'])->name('member.clock-in');
        Route::post('/member/clock-out', [\App\Http\Controllers\AttendanceController::class, 'clockOut'])->name('member.clock-out');
        
        Route::get('/member/leaves/create', [\App\Http\Controllers\LeaveRequestController::class, 'create'])->name('member.leaves.create');
        Route::post('/member/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'store'])->name('member.leaves.store');

        // Modul Rapor Digital Siswa (Sprint 4)
        Route::get('/student/report-card', [\App\Http\Controllers\Student\StudentReportCardController::class, 'studentIndex'])->name('student.report-card');
        Route::get('/student/report-card/print', [\App\Http\Controllers\Student\StudentReportCardController::class, 'studentPrint'])->name('student.report-card.print');
    });

    // Dismiss Announcements
    Route::post('/student/announcements/{announcement}/dismiss', [\App\Http\Controllers\MemberDashboardController::class, 'dismissAnnouncement'])->name('student.announcements.dismiss');
    Route::post('/student/announcements/{announcement}/restore', [\App\Http\Controllers\MemberDashboardController::class, 'restoreAnnouncement'])->name('student.announcements.restore');

    // Parent Routes
    Route::middleware([\App\Http\Middleware\RoleMiddleware::class.':parent'])->group(function () {
        Route::get('/parent/dashboard', [\App\Http\Controllers\ParentDashboardController::class, 'index'])->name('parent.dashboard');

        // Modul Rapor Digital Anak (Sprint 4)
        Route::get('/parent/report-card', [\App\Http\Controllers\Student\StudentReportCardController::class, 'parentIndex'])->name('parent.report-card');
        Route::get('/parent/report-card/print', [\App\Http\Controllers\Student\StudentReportCardController::class, 'parentPrint'])->name('parent.report-card.print');
    });

    // Common Auth Routes
    Route::get('/logout', [\App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'destroy'])->name('logout.get');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Support Tickets (User Submission & History)
    Route::get('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'index'])->name('support-tickets.index');
    Route::post('/support-tickets', [\App\Http\Controllers\SupportTicketController::class, 'store'])->name('support-tickets.store');
    Route::get('/support-tickets/{ticket}', [\App\Http\Controllers\SupportTicketController::class, 'show'])->name('support-tickets.show');

    Route::get('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'index'])->name('onboarding');
    Route::post('/onboarding', [\App\Http\Controllers\OnboardingController::class, 'store']);

    // Cetak Lembar Rapor P5 Mandiri Siswa / Anak
    Route::get('/p5/projects/{project}/print/{student}', [\App\Http\Controllers\P5\P5ReportPrintController::class, 'printSingle'])->name('p5.reports.print-single');

    // Cetak Lembar Rapor PKL & Transkrip UKK Vokasi SMK
    Route::get('/vocational/internships/{placement}/print', [\App\Http\Controllers\Vocational\InternshipAssessmentController::class, 'printSingle'])->name('vocational.internships.print');
    Route::get('/vocational/classes/{class}/ukk/print/{student}', [\App\Http\Controllers\Vocational\VocationalCompetencyController::class, 'printSingle'])->name('vocational.ukk.print');
});

Route::middleware('guest')->group(function () {
    Route::get('/register/super-admin/{token}', [\App\Http\Controllers\Auth\SuperAdminRegistrationController::class, 'create'])
        ->name('register.super-admin');
    Route::post('/register/super-admin/{token}', [\App\Http\Controllers\Auth\SuperAdminRegistrationController::class, 'store']);

    Route::get('/register/staff/{token}', [\App\Http\Controllers\Auth\StaffRegistrationController::class, 'create'])
        ->name('register.staff');
    Route::post('/register/staff/{token}', [\App\Http\Controllers\Auth\StaffRegistrationController::class, 'store'])
        ->name('register.staff.store');

    Route::get('/register/member', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'create'])
        ->name('register.member');
    Route::post('/register/member', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'store']);

    // API Routes for Tenant Search (Accessible by guests for registration)
    Route::get('/api/tenants/search', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'searchTenants']);
    Route::get('/api/tenants/check-code', [\App\Http\Controllers\Auth\MemberRegistrationController::class, 'checkCode']);
    
    // Force Password Change Routes
    Route::get('/change-password', function () {
        return view('auth.change-password');
    })->name('password.change');

    Route::post('/change-password', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults(), 'confirmed'],
        ]);

        $request->user()->update([
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
            'must_change_password' => false,
        ]);

        return redirect()->route('dashboard')->with('success', 'Kata sandi berhasil diperbarui.');
    })->name('password.change.store');
});

// Verifikasi Publik Keaslian Dokumen Rapor Digital (Scan QR Code Bebas Autentikasi)
Route::get('/verify-report/{hash}', [\App\Http\Controllers\ReportVerificationController::class, 'verify'])
    ->name('report.verify');

// Pusat Panduan & Tutorial Modul Rapor HadirYuk (Akses Publik / Guest Friendly)
Route::get('/panduan/rapor', [\App\Http\Controllers\PublicGuideController::class, 'rapor'])
    ->name('panduan.rapor');
Route::get('/guide/rapor', [\App\Http\Controllers\PublicGuideController::class, 'rapor'])
    ->name('guide.rapor');

require __DIR__.'/auth.php';
