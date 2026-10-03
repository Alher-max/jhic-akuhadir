<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Services\ReportExportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportController extends Controller
{
    public function __construct(
        protected ReportExportService $exportService
    ) {}

    /**
     * Memeriksa otorisasi umum guru / staf akademik.
     */
    protected function authorizeStaff(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Halaman khusus pendidik dan operator.');
    }

    /**
     * Memastikan guru berhak mengajar kelas & mapel target, atau memiliki hak supervisi.
     */
    protected function authorizeTeacherForClassAndSubject(SchoolClass $class, Subject $subject): void
    {
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $subject->tenant_id !== (int) $user->tenant_id, 404);

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return;
        }

        $assigned = ClassSchedule::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $user->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->exists();

        abort_unless($assigned, 403, 'Akses ditolak. Anda tidak ditugaskan mengajar di kelas dan mata pelajaran ini.');
    }

    /**
     * Memastikan guru adalah wali kelas dari rombel bersangkutan, atau memiliki hak supervisi.
     */
    protected function authorizeHomeroomForClass(SchoolClass $class): void
    {
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return;
        }

        abort_unless((int) $class->wali_kelas_id === (int) $user->id, 403, 'Akses ditolak. Anda bukan wali kelas dari rombel ini.');
    }

    /**
     * Ekspor nilai mapel rombel ke format e-Rapor SP Kemendikbudristek (CSV/Excel).
     */
    public function exportERapor(Request $request, SchoolClass $class, Subject $subject): StreamedResponse
    {
        $this->authorizeStaff();
        $this->authorizeTeacherForClassAndSubject($class, $subject);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif untuk ekspor nilai.');

        return $this->exportService->streamSubjectGradeForERapor($class, $subject, $activeYear);
    }

    /**
     * Ekspor nilai mapel rombel ke format RDM Kemenag (Rapor Digital Madrasah).
     */
    public function exportRDM(Request $request, SchoolClass $class, Subject $subject): StreamedResponse
    {
        $this->authorizeStaff();
        $this->authorizeTeacherForClassAndSubject($class, $subject);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif untuk ekspor nilai.');

        return $this->exportService->streamRDMExport($class, $subject, $activeYear);
    }

    /**
     * Ekspor rekapitulasi leger lengkap kelas ke format Dapodik / Arsip Nilai (CSV/Excel).
     */
    public function exportLeger(Request $request, SchoolClass $class): StreamedResponse
    {
        $this->authorizeStaff();
        $this->authorizeHomeroomForClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif untuk ekspor leger.');

        return $this->exportService->streamClassLeger($class, $activeYear);
    }
}
