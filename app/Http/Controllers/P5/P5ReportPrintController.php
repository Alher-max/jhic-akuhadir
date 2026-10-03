<?php

declare(strict_types=1);

namespace App\Http\Controllers\P5;

use App\Http\Controllers\Controller;
use App\Models\P5Assessment;
use App\Models\P5Project;
use App\Models\StudentReport;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class P5ReportPrintController extends Controller
{
    /**
     * Cetak lembar Rapor P5 untuk 1 siswa (A4 Portrait).
     */
    public function printSingle(Request $request, P5Project $project, User $student): View
    {
        $user = auth()->user();
        abort_if((int) $project->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $student->tenant_id !== (int) $user->tenant_id, 404);
        abort_if((int) $student->class_id !== (int) $project->class_id, 404);

        $role = $user->role;

        // Validasi akses untuk Siswa & Orang Tua
        if (in_array($role, ['student', 'siswa'], true)) {
            abort_unless((int) $user->id === (int) $student->id, 403, 'Akses ditolak. Anda hanya dapat melihat rapor Anda sendiri.');

            // Publication Guard: Rapor harus berstatus published / locked
            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('academic_year_id', $project->academic_year_id)
                ->where('student_id', $student->id)
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Rapor projek belum dipublikasikan oleh pihak sekolah.');
        } elseif (in_array($role, ['parent', 'ortu'], true)) {
            $isChild = $user->students()->where('users.id', $student->id)->exists();
            abort_unless($isChild, 403, 'Akses ditolak. Anda bukan orang tua / wali dari siswa ini.');

            $studentReport = StudentReport::withoutGlobalScope('tenant')
                ->where('tenant_id', $user->tenant_id)
                ->where('academic_year_id', $project->academic_year_id)
                ->where('student_id', $student->id)
                ->first();

            abort_unless($studentReport && in_array($studentReport->status, ['published', 'locked'], true), 403, 'Rapor projek belum dipublikasikan oleh pihak sekolah.');
        } else {
            // Staf / Guru: Harus Koordinator, Wali Kelas, atau Pimpinan
            $isCoordinator = (int) $project->coordinator_id === (int) $user->id;
            $isHomeroom = (int) $project->schoolClass?->wali_kelas_id === (int) $user->id;
            $isElevated = in_array($role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);

            abort_unless($isCoordinator || $isHomeroom || $isElevated, 403, 'Akses ditolak. Anda bukan koordinator projek atau wali kelas dari rombel ini.');
        }

        $project->load([
            'academicYear',
            'schoolClass.waliKelas',
            'coordinator',
            'targets',
        ]);

        $tenant = $project->tenant;

        // Ambil penilaian siswa ini
        $assessments = P5Assessment::withoutGlobalScope('tenant')
            ->where('tenant_id', $project->tenant_id)
            ->where('p5_project_id', $project->id)
            ->where('student_id', $student->id)
            ->get()
            ->keyBy('p5_project_target_id');

        // Ambil catatan proses siswa
        $studentNote = $project->studentNotes()
            ->where('student_id', $student->id)
            ->first();

        // Cari Kepala Sekolah
        $headmaster = User::where('tenant_id', $project->tenant_id)
            ->whereIn('role', ['headmaster', 'kepala_sekolah'])
            ->first();

        return view('p5.reports.print-single', compact('project', 'student', 'tenant', 'assessments', 'studentNote', 'headmaster'));
    }

    /**
     * Cetak lembar Rapor P5 massal satu rombel kelas (A4 Portrait dengan .page-break).
     */
    public function printBatch(Request $request, P5Project $project): View
    {
        $user = auth()->user();
        abort_if((int) $project->tenant_id !== (int) $user->tenant_id, 404);

        $role = $user->role;
        $isCoordinator = (int) $project->coordinator_id === (int) $user->id;
        $isHomeroom = (int) $project->schoolClass?->wali_kelas_id === (int) $user->id;
        $isElevated = in_array($role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);

        abort_unless($isCoordinator || $isHomeroom || $isElevated, 403, 'Akses ditolak. Cetak massal hanya untuk fasilitator projek dan pimpinan.');

        $project->load([
            'academicYear',
            'schoolClass.waliKelas',
            'schoolClass.students' => fn($q) => $q->orderBy('name'),
            'coordinator',
            'targets',
        ]);

        $tenant = $project->tenant;
        $students = $project->schoolClass->students;

        // Ambil seluruh penilaian rombel di projek ini
        $allAssessments = P5Assessment::withoutGlobalScope('tenant')
            ->where('tenant_id', $project->tenant_id)
            ->where('p5_project_id', $project->id)
            ->get();

        $assessmentsByStudent = [];
        foreach ($allAssessments as $assessment) {
            $assessmentsByStudent[$assessment->student_id][$assessment->p5_project_target_id] = $assessment;
        }

        // Ambil seluruh catatan proses siswa
        $studentNotes = $project->studentNotes->keyBy('student_id');

        // Cari Kepala Sekolah
        $headmaster = User::where('tenant_id', $project->tenant_id)
            ->whereIn('role', ['headmaster', 'kepala_sekolah'])
            ->first();

        return view('p5.reports.print-batch', compact('project', 'students', 'tenant', 'assessmentsByStudent', 'studentNotes', 'headmaster'));
    }
}
