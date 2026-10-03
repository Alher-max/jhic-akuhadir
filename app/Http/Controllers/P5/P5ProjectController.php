<?php

declare(strict_types=1);

namespace App\Http\Controllers\P5;

use App\Http\Controllers\Controller;
use App\Http\Requests\P5\StoreP5ProjectRequest;
use App\Http\Requests\P5\UpdateP5ProjectRequest;
use App\Models\AcademicYear;
use App\Models\P5Project;
use App\Models\SchoolClass;
use App\Models\User;
use App\Services\P5PresetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class P5ProjectController extends Controller
{
    public function __construct(
        protected P5PresetService $presetService
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
     * Memeriksa apakah user memiliki peran pimpinan atau operator.
     */
    protected function isElevated(): bool
    {
        $user = auth()->user();
        return in_array($user?->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
    }

    /**
     * Memeriksa hak mengelola projek pada kelas (Wali Kelas atau Pimpinan).
     */
    protected function authorizeManageClass(SchoolClass $class): void
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
     * Memeriksa hak mengelola projek (Koordinator, Wali Kelas, atau Pimpinan).
     */
    protected function authorizeManageProject(P5Project $project): void
    {
        $user = auth()->user();
        abort_if((int) $project->tenant_id !== (int) $user->tenant_id, 404);

        if ($this->isElevated()) {
            return;
        }

        $isCoordinator = (int) $project->coordinator_id === (int) $user->id;
        $isHomeroom = (int) $project->schoolClass?->wali_kelas_id === (int) $user->id;

        abort_unless($isCoordinator || $isHomeroom, 403, 'Akses ditolak. Anda bukan koordinator projek atau wali kelas dari rombel ini.');
    }

    /**
     * Tampilan umum daftar projek: memilih kelas yang tersedia.
     */
    public function indexAll(Request $request): View|RedirectResponse
    {
        $this->authorizeStaff();
        $user = auth()->user();

        // Cari rombel wali kelas terlebih dahulu jika ada
        $homeroomClass = SchoolClass::where('tenant_id', $user->tenant_id)
            ->where('wali_kelas_id', $user->id)
            ->first();

        if ($homeroomClass && !$request->has('class_id')) {
            return redirect()->route('p5.projects.index', $homeroomClass);
        }

        if ($request->filled('class_id')) {
            $targetClass = SchoolClass::where('tenant_id', $user->tenant_id)->findOrFail($request->class_id);
            return redirect()->route('p5.projects.index', $targetClass);
        }

        $classes = SchoolClass::where('tenant_id', $user->tenant_id)
            ->orderBy('tingkat')
            ->orderBy('nama_kelas')
            ->get();

        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();

        return view('p5.projects.select-class', compact('classes', 'activeYear'));
    }

    /**
     * Menampilkan daftar projek P5/P5RA per rombel kelas.
     */
    public function index(Request $request, SchoolClass $class): View
    {
        $this->authorizeStaff();
        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);

        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();

        $projects = P5Project::where('tenant_id', $user->tenant_id)
            ->where('class_id', $class->id)
            ->when($activeYear, fn($q) => $q->where('academic_year_id', $activeYear->id))
            ->with(['coordinator', 'targets'])
            ->withCount(['targets', 'assessments'])
            ->latest()
            ->get();

        $studentCount = $class->students()->count();

        $isHomeroomOrElevated = $this->isElevated() || ((int) $class->wali_kelas_id === (int) $user->id);

        return view('p5.projects.index', compact('class', 'projects', 'activeYear', 'studentCount', 'isHomeroomOrElevated'));
    }

    /**
     * Form pembuatan projek baru untuk kelas.
     */
    public function create(SchoolClass $class): View
    {
        $this->authorizeStaff();
        $this->authorizeManageClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif untuk membuat projek.');

        $teachers = User::where('tenant_id', $user->tenant_id)
            ->whereIn('role', ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'operator', 'admin', 'headmaster', 'kepala_sekolah'])
            ->orderBy('name')
            ->get();

        $themes = $this->presetService->getThemes();
        $pancasilaDimensions = $this->presetService->getPancasilaDimensions();
        $rahmatanValues = $this->presetService->getRahmatanLilAlaminValues();

        return view('p5.projects.create', compact('class', 'activeYear', 'teachers', 'themes', 'pancasilaDimensions', 'rahmatanValues'));
    }

    /**
     * Menyimpan projek P5 baru beserta target awal.
     */
    public function store(StoreP5ProjectRequest $request, SchoolClass $class): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizeManageClass($class);

        $user = auth()->user();
        $activeYear = AcademicYear::where('tenant_id', $user->tenant_id)->active()->first();
        abort_unless($activeYear, 422, 'Tidak ada tahun ajaran aktif.');

        $validated = $request->validated();

        $coordinatorId = !empty($validated['coordinator_id']) ? (int) $validated['coordinator_id'] : (int) $user->id;

        $project = DB::transaction(function () use ($user, $activeYear, $class, $coordinatorId, $validated) {
            $proj = P5Project::create([
                'tenant_id' => $user->tenant_id,
                'academic_year_id' => $activeYear->id,
                'class_id' => $class->id,
                'coordinator_id' => $coordinatorId,
                'theme' => $validated['theme'],
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            foreach ($validated['targets'] as $targetData) {
                $proj->targets()->create([
                    'tenant_id' => $user->tenant_id,
                    'target_type' => $targetData['target_type'],
                    'dimension' => $targetData['dimension'],
                    'element' => $targetData['element'] ?? null,
                    'sub_element' => $targetData['sub_element'],
                    'target_description' => $targetData['target_description'] ?? null,
                ]);
            }

            return $proj;
        });

        return redirect()->route('p5.projects.show', $project)
            ->with('success', 'Projek P5 berhasil dibuat! Silakan lanjutkan dengan pengisian nilai penilaian.');
    }

    /**
     * Dasbor lembar kerja & rincian target projek.
     */
    public function show(P5Project $project): View
    {
        $this->authorizeStaff();
        $this->authorizeManageProject($project);

        $project->load([
            'academicYear',
            'schoolClass.students' => fn($q) => $q->orderBy('name'),
            'coordinator',
            'targets.assessments',
            'studentNotes',
        ]);

        $students = $project->schoolClass->students;
        $totalTargets = $project->targets->count();
        $totalAssessed = $project->assessments->count();
        $expectedAssessments = $students->count() * $totalTargets;
        $progressPercent = $expectedAssessments > 0 ? (int) round(($totalAssessed / $expectedAssessments) * 100) : 0;

        return view('p5.projects.show', compact('project', 'students', 'progressPercent'));
    }

    /**
     * Menghapus projek beserta target dan nilainya.
     */
    public function destroy(P5Project $project): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizeManageProject($project);

        $class = $project->schoolClass;

        DB::transaction(function () use ($project) {
            $project->assessments()->delete();
            $project->studentNotes()->delete();
            $project->targets()->delete();
            $project->delete();
        });

        return redirect()->route('p5.projects.index', $class)
            ->with('success', 'Projek P5 dan seluruh asesmen terkait berhasil dihapus.');
    }
}
