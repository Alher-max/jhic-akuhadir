<?php

declare(strict_types=1);

namespace App\Http\Controllers\P5;

use App\Http\Controllers\Controller;
use App\Http\Requests\P5\BatchStoreP5AssessmentRequest;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\P5StudentNote;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class P5AssessmentController extends Controller
{
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
     * Memeriksa hak mengelola projek (Koordinator, Wali Kelas, atau Pimpinan).
     */
    protected function authorizeManageProject(P5Project $project): void
    {
        $user = auth()->user();
        abort_if((int) $project->tenant_id !== (int) $user->tenant_id, 404);

        $isElevated = in_array($user?->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return;
        }

        $isCoordinator = (int) $project->coordinator_id === (int) $user->id;
        $isHomeroom = (int) $project->schoolClass?->wali_kelas_id === (int) $user->id;

        abort_unless($isCoordinator || $isHomeroom, 403, 'Akses ditolak. Anda bukan koordinator projek atau wali kelas dari rombel ini.');
    }

    /**
     * Menampilkan lembar matriks penilaian siswa × target sub-elemen projek.
     */
    public function matrix(P5Project $project): View
    {
        $this->authorizeStaff();
        $this->authorizeManageProject($project);

        $project->load([
            'academicYear',
            'schoolClass.students' => fn($q) => $q->orderBy('name'),
            'targets',
            'assessments',
            'studentNotes',
            'coordinator',
        ]);

        $students = $project->schoolClass->students;
        $targets = $project->targets;

        // Buat map penilaian: [student_id => [target_id => P5Assessment]]
        $assessmentsMap = [];
        foreach ($project->assessments as $assessment) {
            $assessmentsMap[$assessment->student_id][$assessment->p5_project_target_id] = $assessment;
        }

        // Buat map catatan proses: [student_id => P5StudentNote]
        $studentNotesMap = $project->studentNotes->keyBy('student_id');

        $predicates = [
            P5Assessment::PREDICATE_MB => ['code' => 'MB', 'label' => 'Mulai Berkembang', 'color' => 'red'],
            P5Assessment::PREDICATE_SB => ['code' => 'SB', 'label' => 'Sedang Berkembang', 'color' => 'amber'],
            P5Assessment::PREDICATE_BSH => ['code' => 'BSH', 'label' => 'Berkembang Sesuai Harapan', 'color' => 'blue'],
            P5Assessment::PREDICATE_SAB => ['code' => 'SAB', 'label' => 'Sangat Berkembang', 'color' => 'emerald'],
        ];

        return view('p5.assessments.matrix', compact('project', 'students', 'targets', 'assessmentsMap', 'studentNotesMap', 'predicates'));
    }

    /**
     * Menyimpan massal predikat capaian siswa dan catatan proses fasilitator.
     */
    public function batchStore(BatchStoreP5AssessmentRequest $request, P5Project $project): RedirectResponse
    {
        $this->authorizeStaff();
        $this->authorizeManageProject($project);

        $validated = $request->validated();
        $studentIds = $project->schoolClass->students()->pluck('users.id')->all();
        $targetIds = $project->targets()->pluck('id')->all();

        DB::transaction(function () use ($project, $validated, $studentIds, $targetIds) {
            // 1. Simpan Predikat Asesmen Siswa per Sub-elemen
            $assessmentsData = $validated['assessments'] ?? [];

            foreach ($assessmentsData as $studentId => $targetPredicates) {
                $studentId = (int) $studentId;
                if (!in_array($studentId, $studentIds, true)) {
                    continue;
                }

                foreach ($targetPredicates as $targetId => $predicate) {
                    $targetId = (int) $targetId;
                    if (!in_array($targetId, $targetIds, true)) {
                        continue;
                    }

                    if (empty($predicate)) {
                        P5Assessment::withoutGlobalScope('tenant')
                            ->where('tenant_id', $project->tenant_id)
                            ->where('p5_project_target_id', $targetId)
                            ->where('student_id', $studentId)
                            ->delete();
                        continue;
                    }

                    P5Assessment::withoutGlobalScope('tenant')->updateOrCreate(
                        [
                            'tenant_id' => $project->tenant_id,
                            'p5_project_target_id' => $targetId,
                            'student_id' => $studentId,
                        ],
                        [
                            'p5_project_id' => $project->id,
                            'predicate' => $predicate,
                        ]
                    );
                }
            }

            // 2. Simpan Catatan Proses Fasilitator
            $notesData = $validated['notes'] ?? [];

            foreach ($notesData as $studentId => $processNotes) {
                $studentId = (int) $studentId;
                if (!in_array($studentId, $studentIds, true)) {
                    continue;
                }

                if (empty($processNotes)) {
                    P5StudentNote::withoutGlobalScope('tenant')
                        ->where('tenant_id', $project->tenant_id)
                        ->where('p5_project_id', $project->id)
                        ->where('student_id', $studentId)
                        ->delete();
                    continue;
                }

                P5StudentNote::withoutGlobalScope('tenant')->updateOrCreate(
                    [
                        'tenant_id' => $project->tenant_id,
                        'p5_project_id' => $project->id,
                        'student_id' => $studentId,
                    ],
                    [
                        'process_notes' => $processNotes,
                    ]
                );
            }
        });

        return redirect()->route('p5.assessments.matrix', $project)
            ->with('success', 'Penilaian capaian projek dan catatan proses siswa berhasil disimpan!');
    }
}
