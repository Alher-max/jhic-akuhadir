<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vocational;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vocational\StoreInternshipAssessmentRequest;
use App\Models\InternshipAssessment;
use App\Models\InternshipPlacement;
use App\Models\StudentReport;
use App\Models\User;
use App\Services\VocationalReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InternshipAssessmentController extends Controller
{
    public function __construct(
        protected VocationalReportService $vocationalService
    ) {}

    protected function authorizeStaff(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Halaman khusus pendidik dan operator.');
    }

    protected function isElevated(): bool
    {
        $user = auth()->user();
        return in_array($user?->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
    }

    protected function authorizePlacement(InternshipPlacement $placement): void
    {
        $user = auth()->user();
        abort_if((int) $placement->tenant_id !== (int) $user->tenant_id, 404);

        if ($this->isElevated()) {
            return;
        }

        $isSupervisor = (int) $placement->teacher_supervisor_id === (int) $user->id;
        $isHomeroom = (int) $placement->schoolClass?->wali_kelas_id === (int) $user->id;

        abort_unless($isSupervisor || $isHomeroom, 403, 'Akses ditolak. Anda bukan pembimbing PKL atau wali kelas dari siswa ini.');
    }

    /**
     * Lembar Input Nilai PKL Siswa.
     */
    public function show(InternshipPlacement $placement): View
    {
        $this->authorizeStaff();
        $this->authorizePlacement($placement);

        $placement->load(['student', 'schoolClass', 'teacherSupervisor', 'industryLocation', 'academicYear', 'assessment']);

        // Auto-pull rasio presensi industri siswa
        $calculatedAttendanceScore = $this->vocationalService->calculatePlacementAttendanceScore($placement);

        return view('vocational.internships.assessment', compact('placement', 'calculatedAttendanceScore'));
    }

    /**
     * Simpan / Perbarui Nilai PKL Siswa.
     */
    public function store(StoreInternshipAssessmentRequest $request, InternshipPlacement $placement): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizePlacement($placement);

        $validated = $request->validated();

        $tech = (float) $validated['technical_score'];
        $soft = (float) $validated['softskill_score'];
        $att = (float) $validated['attendance_score'];

        $finalScore = $this->vocationalService->calculateFinalScore($tech, $soft, $att);
        $predicate = $this->vocationalService->determinePredicate($finalScore);

        DB::transaction(function () use ($placement, $tech, $soft, $att, $finalScore, $predicate, $validated) {
            InternshipAssessment::updateOrCreate(
                [
                    'tenant_id' => $placement->tenant_id,
                    'internship_placement_id' => $placement->id,
                ],
                [
                    'technical_score' => $tech,
                    'softskill_score' => $soft,
                    'attendance_score' => $att,
                    'final_score' => $finalScore,
                    'predicate' => $predicate,
                    'technical_notes' => $validated['technical_notes'] ?? null,
                    'softskill_notes' => $validated['softskill_notes'] ?? null,
                ]
            );
        });

        return redirect()->route('vocational.internships.assessment.show', $placement)
            ->with('success', 'Nilai PKL dan catatan evaluasi industri berhasil disimpan!');
    }

    /**
     * Cetak Lembar Laporan/Sertifikat Nilai PKL Resmi A4.
     */
    public function printSingle(Request $request, InternshipPlacement $placement): View
    {
        $user = auth()->user();
        abort_if((int) $placement->tenant_id !== (int) $user->tenant_id, 404);

        $role = $user->role;

        // Publication Guard untuk Siswa & Orang Tua
        if (in_array($role, ['student', 'siswa'], true)) {
            abort_unless((int) $user->id === (int) $placement->student_id, 403, 'Akses ditolak.');

            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('academic_year_id', $placement->academic_year_id)
                ->where('student_id', $placement->student_id)
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Laporan nilai belum dipublikasikan oleh pihak sekolah.');
        } elseif (in_array($role, ['parent', 'ortu'], true)) {
            $isChild = $user->students()->where('users.id', $placement->student_id)->exists();
            abort_unless($isChild, 403, 'Akses ditolak.');

            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('academic_year_id', $placement->academic_year_id)
                ->where('student_id', $placement->student_id)
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Laporan nilai belum dipublikasikan oleh pihak sekolah.');
        } else {
            // Guru Pembimbing, Wali Kelas, atau Pimpinan
            $isSupervisor = (int) $placement->teacher_supervisor_id === (int) $user->id;
            $isHomeroom = (int) $placement->schoolClass?->wali_kelas_id === (int) $user->id;
            $isElevated = $this->isElevated();

            abort_unless($isSupervisor || $isHomeroom || $isElevated, 403, 'Akses ditolak.');
        }

        $placement->load(['student', 'schoolClass.waliKelas', 'teacherSupervisor', 'industryLocation', 'academicYear', 'assessment']);

        $assessment = $placement->assessment;
        $tenant = $placement->tenant;

        $headmaster = User::where('tenant_id', $placement->tenant_id)
            ->whereIn('role', ['headmaster', 'kepala_sekolah'])
            ->first();

        return view('vocational.internships.print-single', compact('placement', 'assessment', 'tenant', 'headmaster'));
    }
}
