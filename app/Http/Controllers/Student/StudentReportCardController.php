<?php

declare(strict_types=1);

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\AcademicYear;
use App\Models\StudentReport;
use App\Models\User;
use App\Services\ReportCardRendererService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentReportCardController extends Controller
{
    public function __construct(
        protected ReportCardRendererService $rendererService
    ) {}

    /**
     * Tampilan portal rapor digital untuk akun Siswa.
     */
    public function studentIndex(Request $request): View
    {
        $student = auth()->user();
        abort_unless($student && $student->role === 'student', 403, 'Akses khusus akun siswa.');

        $tenantId = (int) $student->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $report = null;
        $reportData = null;
        $isPublished = false;

        if ($activeYear && $student->class_id) {
            $report = StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $student->class_id)
                ->where('student_id', $student->id)
                ->first();

            if ($report && $report->isPublished()) {
                $isPublished = true;
                $reportData = $this->rendererService->getStudentReportCardData($report);
            }
        }

        return view('student.report-card', [
            'student' => $student,
            'activeYear' => $activeYear,
            'report' => $report,
            'reportData' => $reportData,
            'isPublished' => $isPublished,
        ]);
    }

    /**
     * Buka lembar cetak A4 resmi untuk siswa yang sedang login.
     */
    public function studentPrint(Request $request): View
    {
        $student = auth()->user();
        abort_unless($student && $student->role === 'student', 403, 'Akses khusus akun siswa.');

        $tenantId = (int) $student->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        abort_unless($activeYear, 404, 'Tahun ajaran aktif tidak ditemukan.');

        $report = StudentReport::where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('student_id', $student->id)
            ->first();

        abort_unless($report && $report->isPublished(), 403, 'Rapor semester ini sedang dalam proses penyusunan dan belum dipublikasikan oleh pihak sekolah.');

        $data = $this->rendererService->getStudentReportCardData($report);

        return view('reports.print-single', $data);
    }

    /**
     * Tampilan portal rapor digital untuk akun Orang Tua / Wali Murid.
     */
    public function parentIndex(Request $request): View
    {
        $parent = auth()->user();
        abort_unless($parent && $parent->role === 'parent', 403, 'Akses khusus akun orang tua.');

        $tenantId = (int) $parent->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        // Ambil seluruh anak dari orang tua yang login
        $children = $parent->students()
            ->where('users.tenant_id', $tenantId)
            ->with(['schoolClass'])
            ->get()
            ->merge(
                $parent->children()
                    ->where('tenant_id', $tenantId)
                    ->with(['schoolClass'])
                    ->get()
            )
            ->unique('id')
            ->values();

        $selectedChild = null;
        if ($children->isNotEmpty()) {
            if ($request->filled('child_id')) {
                $selectedChild = $children->firstWhere('id', (int) $request->child_id);
            }
            $selectedChild = $selectedChild ?? $children->first();
        }

        $report = null;
        $reportData = null;
        $isPublished = false;

        if ($selectedChild && $activeYear && $selectedChild->class_id) {
            $report = StudentReport::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('student_id', $selectedChild->id)
                ->first();

            if ($report && $report->isPublished()) {
                $isPublished = true;
                $reportData = $this->rendererService->getStudentReportCardData($report);
            }
        }

        return view('parent.report-card', [
            'parent' => $parent,
            'children' => $children,
            'selectedChild' => $selectedChild,
            'activeYear' => $activeYear,
            'report' => $report,
            'reportData' => $reportData,
            'isPublished' => $isPublished,
        ]);
    }

    /**
     * Buka lembar cetak A4 resmi untuk anak yang dipilih oleh orang tua.
     */
    public function parentPrint(Request $request): View
    {
        $parent = auth()->user();
        abort_unless($parent && $parent->role === 'parent', 403, 'Akses khusus akun orang tua.');

        $tenantId = (int) $parent->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        abort_unless($activeYear, 404, 'Tahun ajaran aktif tidak ditemukan.');

        $children = $parent->students()
            ->where('users.tenant_id', $tenantId)
            ->get()
            ->merge(
                $parent->children()
                    ->where('tenant_id', $tenantId)
                    ->get()
            )
            ->unique('id')
            ->values();

        $childId = (int) $request->input('child_id');
        $selectedChild = $children->firstWhere('id', $childId) ?? $children->first();

        abort_unless($selectedChild, 404, 'Data anak tidak ditemukan.');

        $report = StudentReport::where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('student_id', $selectedChild->id)
            ->first();

        abort_unless($report && $report->isPublished(), 403, 'Rapor semester ini sedang dalam proses penyusunan dan belum dipublikasikan oleh pihak sekolah.');

        $data = $this->rendererService->getStudentReportCardData($report);

        return view('reports.print-single', $data);
    }
}
