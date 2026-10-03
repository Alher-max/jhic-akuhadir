<?php

declare(strict_types=1);

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\BatchStoreSubjectGradeRequest;
use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\LearningObjective;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\SubjectGrade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GradebookController extends Controller
{
    /**
     * Memeriksa otorisasi umum pengguna untuk mengakses modul buku nilai.
     */
    protected function authorizeTeacher(): void
    {
        $role = auth()->user()?->role;
        $allowed = ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        abort_unless(in_array($role, $allowed, true), 403, 'Akses ditolak. Halaman khusus pendidik.');
    }

    /**
     * Memastikan guru memiliki penugasan jadwal pada kombinasi kelas & mapel bersangkutan.
     */
    protected function authorizeTeacherForClassAndSubject(int $classId, int $subjectId): void
    {
        $user = auth()->user();
        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return;
        }

        $assigned = ClassSchedule::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();

        abort_unless($assigned, 403, 'Akses ditolak. Anda tidak ditugaskan mengajar di kelas dan mata pelajaran ini.');
    }

    /**
     * Menampilkan daftar penugasan kelas & mapel yang diampu oleh guru yang sedang login.
     */
    public function index(Request $request): View
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);

        $query = ClassSchedule::where('tenant_id', $tenantId)
            ->with(['schoolClass', 'subject', 'teacher']);

        if (!$isElevated) {
            $query->where('teacher_id', $user->id);
        }

        // Ambil penugasan unik per pasangan class_id dan subject_id
        $assignments = $query->get()
            ->unique(fn ($item) => "{$item->class_id}_{$item->subject_id}")
            ->values();

        return view('teacher.gradebook.index', compact('assignments', 'activeYear'));
    }

    /**
     * Menampilkan lembar kerja buku nilai siswa untuk kombinasi kelas dan mapel tertentu.
     */
    public function show(Request $request, SchoolClass $class, Subject $subject): View|JsonResponse
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        abort_if((int) $class->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $subject->tenant_id !== (int) $user->tenant_id, 404);

        $this->authorizeTeacherForClassAndSubject($class->id, $subject->id);

        $tenantId = $user->tenant_id;
        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();

        // Ambil siswa dalam rombel ini
        $students = $class->students()->orderBy('name')->get();

        // Ambil nilai yang sudah ada untuk tahun ajaran aktif
        $existingGrades = collect();
        $learningObjectives = collect();

        if ($activeYear) {
            $existingGrades = SubjectGrade::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('class_id', $class->id)
                ->where('subject_id', $subject->id)
                ->get()
                ->keyBy('student_id');

            $learningObjectives = LearningObjective::where('tenant_id', $tenantId)
                ->where('academic_year_id', $activeYear->id)
                ->where('subject_id', $subject->id)
                ->where(function ($q) use ($class) {
                    $q->where('class_id', $class->id)->orWhereNull('class_id');
                })
                ->orderBy('code')
                ->get();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'class' => $class,
                'subject' => $subject,
                'active_year' => $activeYear,
                'students' => $students,
                'existing_grades' => $existingGrades,
                'learning_objectives' => $learningObjectives,
            ]);
        }

        return view('teacher.gradebook.show', compact(
            'class',
            'subject',
            'activeYear',
            'students',
            'existingGrades',
            'learningObjectives'
        ));
    }

    /**
     * Menyimpan nilai siswa secara massal (bulk upsert) atau per baris siswa.
     */
    public function store(BatchStoreSubjectGradeRequest $request): RedirectResponse|JsonResponse
    {
        $this->authorizeTeacher();

        $user = auth()->user();
        $tenantId = $user->tenant_id;

        $activeYear = AcademicYear::where('tenant_id', $tenantId)->active()->first();
        if (!$activeYear) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada tahun ajaran aktif. Penyimpanan nilai dibatalkan.',
                ], 422);
            }
            return back()->with('error', 'Tidak ada tahun ajaran aktif. Penyimpanan nilai dibatalkan.');
        }

        $validated = $request->validated();
        $classId = (int) $validated['class_id'];
        $subjectId = (int) $validated['subject_id'];

        $this->authorizeTeacherForClassAndSubject($classId, $subjectId);

        $savedCount = DB::transaction(function () use ($validated, $tenantId, $activeYear, $classId, $subjectId, $user) {
            $count = 0;
            foreach ($validated['grades'] as $gradeItem) {
                SubjectGrade::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $classId,
                        'subject_id' => $subjectId,
                        'student_id' => (int) $gradeItem['student_id'],
                    ],
                    [
                        'teacher_id' => $user->id,
                        'score' => (float) $gradeItem['score'],
                        'highest_achievement' => !empty($gradeItem['highest_achievement']) ? (string) $gradeItem['highest_achievement'] : null,
                        'lowest_achievement' => !empty($gradeItem['lowest_achievement']) ? (string) $gradeItem['lowest_achievement'] : null,
                    ]
                );
                $count++;
            }
            return $count;
        });

        $message = "Berhasil menyimpan {$savedCount} nilai siswa.";

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
                'saved_count' => $savedCount,
            ]);
        }

        return redirect()->route('teacher.gradebook.show', [$classId, $subjectId])
            ->with('success', $message);
    }
}
