<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\Attendance;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\SubjectGrade;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class HomeroomReportService
{
    /**
     * Menghitung rekapitulasi kehadiran resmi tingkat sekolah (Sakit, Izin, Alpa)
     * menggunakan klausa ANSI SQL murni dengan filter rentang tanggal tahun ajaran aktif.
     *
     * @return array{sick: int, permission: int, alpha: int}
     */
    public function calculateAttendanceSummary(int $tenantId, int $studentId, AcademicYear $activeYear): array
    {
        $startDate = $activeYear->start_date instanceof \DateTimeInterface 
            ? $activeYear->start_date->format('Y-m-d') 
            : (string) $activeYear->start_date;

        $endDate = $activeYear->end_date instanceof \DateTimeInterface 
            ? $activeYear->end_date->format('Y-m-d') 
            : (string) $activeYear->end_date;

        $result = Attendance::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('user_id', $studentId)
            ->where('attendance_type', 'school')
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw("
                SUM(CASE WHEN status = 'sick' THEN 1 ELSE 0 END) as total_sick,
                SUM(CASE WHEN status = 'permission' THEN 1 ELSE 0 END) as total_permission,
                SUM(CASE WHEN status = 'alpha' THEN 1 ELSE 0 END) as total_alpha
            ")
            ->first();

        return [
            'sick' => (int) ($result->total_sick ?? 0),
            'permission' => (int) ($result->total_permission ?? 0),
            'alpha' => (int) ($result->total_alpha ?? 0),
        ];
    }

    /**
     * Melakukan auto-pull sinkronisasi presensi untuk seluruh siswa dalam suatu kelas asuhan.
     */
    public function syncAttendanceForClass(SchoolClass $class, AcademicYear $activeYear, ?int $waliKelasId = null): int
    {
        $tenantId = (int) $class->tenant_id;
        $students = $class->students;

        return DB::transaction(function () use ($students, $class, $activeYear, $tenantId, $waliKelasId) {
            $count = 0;
            foreach ($students as $student) {
                $summary = $this->calculateAttendanceSummary($tenantId, (int) $student->id, $activeYear);

                StudentReport::updateOrCreate(
                    [
                        'tenant_id' => $tenantId,
                        'academic_year_id' => $activeYear->id,
                        'class_id' => $class->id,
                        'student_id' => $student->id,
                    ],
                    [
                        'wali_kelas_id' => $waliKelasId ?? $class->wali_kelas_id,
                        'sick_count' => $summary['sick'],
                        'permission_count' => $summary['permission'],
                        'alpha_count' => $summary['alpha'],
                    ]
                );
                $count++;
            }
            return $count;
        });
    }

    /**
     * Mengompilasi data matriks leger nilai kelas (siswa x mata pelajaran).
     *
     * @return array{
     *     subjects: Collection,
     *     students: Collection,
     *     matrix: array<int, array<int, float|null>>,
     *     averages: array<int, float>,
     *     reports: Collection<int, StudentReport>
     * }
     */
    public function buildLegerMatrix(SchoolClass $class, AcademicYear $activeYear): array
    {
        $tenantId = (int) $class->tenant_id;

        // 1. Ambil seluruh mata pelajaran unik yang diajarkan di kelas ini
        $subjects = ClassSchedule::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get()
            ->pluck('subject')
            ->filter()
            ->unique('id')
            ->values();

        // 2. Ambil seluruh siswa aktif di kelas
        $students = $class->students()->orderBy('name')->get();

        // 3. Ambil seluruh nilai mapel siswa untuk tahun ajaran aktif
        $grades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $class->id)
            ->get();

        // 4. Ambil lembar rapor siswa (rekap presensi & catatan)
        $reports = StudentReport::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $class->id)
            ->with('extracurriculars')
            ->get()
            ->keyBy('student_id');

        // 5. Susun matriks: $matrix[student_id][subject_id] = score
        $matrix = [];
        $averages = [];

        foreach ($students as $student) {
            $studentGrades = $grades->where('student_id', $student->id);
            $totalScore = 0.0;
            $gradedCount = 0;

            foreach ($subjects as $subject) {
                $gradeRecord = $studentGrades->firstWhere('subject_id', $subject->id);
                $score = $gradeRecord ? (float) $gradeRecord->score : null;
                $matrix[$student->id][$subject->id] = $score;

                if ($score !== null) {
                    $totalScore += $score;
                    $gradedCount++;
                }
            }

            $averages[$student->id] = $gradedCount > 0 ? round($totalScore / $gradedCount, 2) : 0.0;
        }

        // 6. Hitung rata-rata per mata pelajaran
        $subjectAverages = [];
        foreach ($subjects as $subject) {
            $colScores = [];
            foreach ($students as $student) {
                if (isset($matrix[$student->id][$subject->id]) && $matrix[$student->id][$subject->id] !== null) {
                    $colScores[] = $matrix[$student->id][$subject->id];
                }
            }
            $subjectAverages[$subject->id] = count($colScores) > 0 ? round(array_sum($colScores) / count($colScores), 2) : 0.0;
        }

        // 7. Hitung rata-rata keseluruhan rombel
        $allStudentAverages = array_filter($averages, fn($v) => $v > 0);
        $overallAverage = count($allStudentAverages) > 0 ? round(array_sum($allStudentAverages) / count($allStudentAverages), 2) : 0.0;

        return [
            'subjects' => $subjects,
            'students' => $students,
            'matrix' => $matrix,
            'averages' => $averages,
            'subjectAverages' => $subjectAverages,
            'overallAverage' => $overallAverage,
            'reports' => $reports,
        ];
    }
}
