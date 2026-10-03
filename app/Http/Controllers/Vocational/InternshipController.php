<?php

declare(strict_types=1);

namespace App\Http\Controllers\Vocational;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vocational\StoreInternshipPlacementRequest;
use App\Models\AcademicYear;
use App\Models\InternshipPlacement;
use App\Models\Location;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InternshipController extends Controller
{
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
        $isSupervisor = InternshipPlacement::where('tenant_id', $user->tenant_id)
            ->where('class_id', $class->id)
            ->where('teacher_supervisor_id', $user->id)
            ->exists();

        abort_unless($isHomeroom || $isSupervisor, 403, 'Akses ditolak. Anda bukan wali kelas atau pembimbing PKL di rombel ini.');
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
     * Daftar Penempatan PKL untuk Rombel Kelas.
     */
    public function index(Request $request, SchoolClass $class): View
    {
        $this->authorizeStaff();
        $this->authorizeClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();

        $placements = InternshipPlacement::where('tenant_id', $user->tenant_id)
            ->where('class_id', $class->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['student', 'teacherSupervisor', 'industryLocation', 'assessment'])
            ->latest()
            ->get();

        $students = $class->students()->orderBy('name')->get();

        return view('vocational.internships.index', compact('class', 'placements', 'students', 'activeYear'));
    }

    /**
     * Form Pendaftaran Penempatan PKL.
     */
    public function create(SchoolClass $class): View
    {
        $this->authorizeStaff();
        $this->authorizeClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif.');

        $students = $class->students()->orderBy('name')->get();

        $teachers = User::where('tenant_id', $user->tenant_id)
            ->whereIn('role', ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'operator', 'admin', 'headmaster', 'kepala_sekolah'])
            ->orderBy('name')
            ->get();

        $locations = Location::where('tenant_id', $user->tenant_id)->orderBy('name')->get();

        return view('vocational.internships.create', compact('class', 'students', 'teachers', 'locations', 'activeYear'));
    }

    /**
     * Simpan Pendaftaran Penempatan PKL.
     */
    public function store(StoreInternshipPlacementRequest $request, SchoolClass $class): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizeClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif.');

        $validated = $request->validated();

        // Validasi siswa milik rombel ini
        $isStudentInClass = $class->students()->where('users.id', $validated['student_id'])->exists();
        abort_unless($isStudentInClass, 422, 'Siswa yang dipilih bukan anggota rombel kelas ini.');

        InternshipPlacement::updateOrCreate(
            [
                'tenant_id' => $user->tenant_id,
                'academic_year_id' => $activeYear->id,
                'student_id' => (int) $validated['student_id'],
            ],
            [
                'class_id' => $class->id,
                'teacher_supervisor_id' => (int) $validated['teacher_supervisor_id'],
                'industry_location_id' => !empty($validated['industry_location_id']) ? (int) $validated['industry_location_id'] : null,
                'company_name' => $validated['company_name'],
                'company_address' => $validated['company_address'] ?? null,
                'mentor_name' => $validated['mentor_name'] ?? null,
                'mentor_position' => $validated['mentor_position'] ?? null,
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
            ]
        );

        return redirect()->route('vocational.internships.index', $class)
            ->with('success', 'Penempatan PKL siswa berhasil didaftarkan!');
    }

    /**
     * Hapus Penempatan PKL.
     */
    public function destroy(InternshipPlacement $placement): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizePlacement($placement);

        $class = $placement->schoolClass;
        $placement->delete();

        return redirect()->route('vocational.internships.index', $class)
            ->with('success', 'Data penempatan PKL berhasil dihapus.');
    }
}
