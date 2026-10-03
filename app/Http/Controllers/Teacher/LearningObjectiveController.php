<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreLearningObjectiveRequest;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\LearningObjective;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningObjectiveController extends Controller
{
    /**
     * Memeriksa otorisasi pengguna untuk mengakses fitur TP.
     */
    protected function authorizeTeacher(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Halaman khusus pendidik.');
    }

    /**
     * Menampilkan daftar Tujuan Pembelajaran (bisa difilter via JSON atau Blade).
     */
    public function index(Request $request): View|JsonResponse
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $query = LearningObjective::where('tenant_id', $tenantId)
            ->with(['subject', 'schoolClass', 'academicYear']);

        if ($activeYear) {
            $query->where('academic_year_id', $activeYear->id);
        }

        if ($request->filled('subject_id')) {
            $query->where('subject_id', (int) $request->input('subject_id'));
        }

        if ($request->filled('class_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('class_id', (int) $request->input('class_id'))
                    ->orWhereNull('class_id');
            });
        }

        $objectives = $query->orderBy('code')->get();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'data' => $objectives,
            ]);
        }

        return view('teacher.learning-objectives.index', compact('objectives', 'activeYear'));
    }

    /**
     * Menyimpan Tujuan Pembelajaran baru.
     */
    public function store(StoreLearningObjectiveRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran yang sedang aktif di sekolah Anda.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran yang sedang aktif.');
        }

        $validated = $request->validated();

        $objective = LearningObjective::create([
            'tenant_id' => $tenantId,
            'academic_year_id' => $activeYear->id,
            'subject_id' => (int) $validated['subject_id'],
            'class_id' => !empty($validated['class_id']) ? (int) $validated['class_id'] : null,
            'code' => $validated['code'],
            'description' => $validated['description'],
        ]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tujuan Pembelajaran berhasil ditambahkan.',
                'data' => $objective->load(['subject', 'schoolClass']),
            ], 201);
        }

        return back()->with('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
    }

    /**
     * Menghapus Tujuan Pembelajaran.
     */
    public function destroy(Request $request, LearningObjective $learningObjective): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        abort_if((int) $learningObjective->tenant_id !== (int) $user->tenant_id, 404);

        // Otorisasi penugasan guru jika bukan operator/kepsek
        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if (!$isElevated) {
            $isAssigned = ClassSchedule::where('tenant_id', $user->tenant_id)
                ->where('teacher_id', $user->id)
                ->where('subject_id', $learningObjective->subject_id)
                ->exists();

            abort_unless($isAssigned, 403, 'Anda tidak memiliki hak untuk menghapus TP mata pelajaran ini.');
        }

        $learningObjective->delete();

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Tujuan Pembelajaran berhasil dihapus.',
            ]);
        }

        return back()->with('success', 'Tujuan Pembelajaran berhasil dihapus.');
    }
}
