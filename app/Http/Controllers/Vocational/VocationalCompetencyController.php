<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vocational;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vocational\BatchStoreUKKRequest;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\User;
use App\Models\VocationalCompetencyAssessment;
use App\Services\VocationalReportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VocationalCompetencyController extends Controller
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

    protected function authorizeClass(SchoolClass $class): void
    {
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);

        if ($this->isElevated()) {
            return;
        }

        $isHomeroom = (int) $class->wali_kelas_id === (int) $user->id;
        abort_unless($isHomeroom, 403, 'Akses ditolak. Anda bukan wali kelas dari rombel ini.');
    }

    /**
     * Rekapitulasi & Lembar Input Nilai UKK per Rombel Kelas.
     */
    public function index(Request $request, SchoolClass $class): View
    {
        $this->authorizeStaff();
        $this->authorizeClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();

        $students = $class->students()->orderBy('name')->get();

        $assessments = VocationalCompetencyAssessment::where('tenant_id', $user->tenant_id)
            ->where('class_id', $class->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->get()
            ->keyBy('student_id');

        return view('vocational.ukk.index', compact('class', 'students', 'assessments', 'activeYear'));
    }

    /**
     * Simpan Massal Nilai UKK Siswa Rombel.
     */
    public function batchStore(BatchStoreUKKRequest $request, SchoolClass $class): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizeClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif.');

        $validated = $request->validated();
        $studentIds = $class->students()->pluck('users.id')->all();

        DB::transaction(function () use ($class, $activeYear, $user, $validated, $studentIds) {
            foreach ($validated['assessments'] as $studentId => $data) {
                $studentId = (int) $studentId;
                if (!in_array($studentId, $studentIds, true)) {
                    continue;
                }

                $practice = (float) $data['practice_score'];
                $theory = isset($data['theory_score']) && $data['theory_score'] !== '' && $data['theory_score'] !== null ? (float) $data['theory_score'] : null;

                // Bobot UKK: 30% Teori + 70% Praktik jika ada teori, atau 100% Praktik jika tanpa teori
                $finalScore = $theory !== null ? round(($theory * 0.30) + ($practice * 0.70), 2) : $practice;
                $predicate = $this->vocationalService->determineUKKPredicate($finalScore);

                VocationalCompetencyAssessment::updateOrCreate(
                    [
                        'tenant_id' => $user->tenant_id,
                        'academic_year_id' => $activeYear->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'class_id' => $class->id,
                        'scheme_name' => $data['scheme_name'],
                        'assessor_name' => $data['assessor_name'],
                        'institution_name' => $data['institution_name'],
                        'theory_score' => $theory,
                        'practice_score' => $practice,
                        'final_score' => $finalScore,
                        'predicate' => $predicate,
                        'certificate_number' => $data['certificate_number'] ?? null,
                    ]
                );
            }
        });

        return redirect()->route('vocational.ukk.index', $class)
            ->with('success', 'Nilai Uji Kompetensi Keahlian (UKK) berhasil disimpan!');
    }

    /**
     * Cetak Lembar Transkrip UKK Resmi A4.
     */
    public function printSingle(Request $request, SchoolClass $class, User $student): View
    {
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $student->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $student->class_id !== (int) $class->id, 404);

        $role = $user->role;

        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();

        // Publication Guard untuk Siswa & Orang Tua
        if (in_array($role, ['student', 'siswa'], true)) {
            abort_unless((int) $user->id === (int) $student->id, 403, 'Akses ditolak.');

            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Transkrip nilai UKK belum dipublikasikan oleh pihak sekolah.');
        } elseif (in_array($role, ['parent', 'ortu'], true)) {
            $isChild = $user->students()->where('users.id', $student->id)->exists();
            abort_unless($isChild, 403, 'Akses ditolak.');

            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('student_id', $student->id)
                ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Transkrip nilai UKK belum dipublikasikan oleh pihak sekolah.');
        } else {
            $isHomeroom = (int) $class->wali_kelas_id === (int) $user->id;
            $isElevated = $this->isElevated();
            abort_unless($isHomeroom || $isElevated, 403, 'Akses ditolak.');
        }

        $assessment = VocationalCompetencyAssessment::where('tenant_id', $user->tenant_id)
            ->where('student_id', $student->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->first();

        abort_unless($assessment, 404, 'Nilai UKK siswa belum diisi.');

        $tenant = $class->tenant;
        $headmaster = User::where('tenant_id', $user->tenant_id)
            ->whereIn('role', ['headmaster', 'kepala_sekolah'])
            ->first();

        return view('vocational.ukk.print-single', compact('class', 'student', 'assessment', 'tenant', 'activeYear', 'headmaster'));
    }
}
