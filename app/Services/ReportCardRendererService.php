<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\SubjectGrade;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Collection;

class ReportCardRendererService
{
    /**
     * Mempersiapkan seluruh data yang dibutuhkan untuk mencetak lembar rapor satu siswa.
     *
     * @param StudentReport $report
     * @return array<string, mixed>
     */
    public function getStudentReportCardData(StudentReport $report): array
    {
        // 1. Eager load relasi penting jika belum termuat
        $report->loadMissing([
            'student',
            'schoolClass.waliKelas',
            'academicYear',
            'extracurriculars',
            'waliKelas',
        ]);

        // Pastikan hash verifikasi tersedia untuk QR code
        if (empty($report->verification_hash)) {
            $report->generateVerificationHash();
        }

        $tenantId = (int) $report->tenant_id;
        $tenant = Tenant::find($tenantId);

        // 2. Ambil Kepala Sekolah / Pimpinan Institusi
        $headmaster = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['headmaster', 'kepala_sekolah', 'owner'])
            ->first();

        // 3. Ambil seluruh nilai mapel siswa untuk tahun ajaran & kelas bersangkutan
        $grades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $report->academic_year_id)
            ->where('class_id', $report->class_id)
            ->where('student_id', $report->student_id)
            ->with('subject')
            ->get()
            ->sortBy(fn($sg) => $sg->subject?->name ?? '')
            ->values();

        // 4. Hitung agregat nilai
        $validScores = $grades->pluck('score')->filter(fn($score) => $score !== null);
        $averageScore = $validScores->isNotEmpty() ? round($validScores->average(), 2) : 0.0;
        $totalScore = $validScores->sum();

        // 5. Data wali kelas
        $waliKelas = $report->waliKelas ?? $report->schoolClass?->waliKelas;

        return [
            'report' => $report,
            'student' => $report->student,
            'schoolClass' => $report->schoolClass,
            'academicYear' => $report->academicYear,
            'tenant' => $tenant,
            'grades' => $grades,
            'extracurriculars' => $report->extracurriculars,
            'attendance' => [
                'sick' => $report->sick_count,
                'permission' => $report->permission_count,
                'alpha' => $report->alpha_count,
                'total' => $report->total_absence,
            ],
            'waliKelas' => $waliKelas,
            'headmaster' => $headmaster,
            'averageScore' => $averageScore,
            'totalScore' => $totalScore,
            'verificationUrl' => $report->verification_url,
        ];
    }

    /**
     * Mempersiapkan seluruh data cetak massal rapor (satu rombel / kelas) dalam satu kueri efisien.
     *
     * @param SchoolClass $class
     * @param AcademicYear $activeYear
     * @return Collection<int, array<string, mixed>>
     */
    public function getClassReportCardsData(SchoolClass $class, AcademicYear $activeYear): Collection
    {
        $tenantId = (int) $class->tenant_id;
        $tenant = Tenant::find($tenantId);

        $headmaster = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['headmaster', 'kepala_sekolah', 'owner'])
            ->first();

        // 1. Ambil seluruh lembar rapor siswa di rombel ini
        $reports = StudentReport::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $class->id)
            ->with([
                'student',
                'schoolClass.waliKelas',
                'academicYear',
                'extracurriculars',
                'waliKelas',
            ])
            ->get();

        // 2. Ambil seluruh nilai mapel rombel dalam 1 query batch
        $allGrades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $activeYear->id)
            ->where('class_id', $class->id)
            ->with('subject')
            ->get()
            ->groupBy('student_id');

        // 3. Susun data tiap siswa
        $cardItems = $reports->map(function (StudentReport $report) use ($class, $activeYear, $tenant, $headmaster, $allGrades) {
            if (empty($report->verification_hash)) {
                $report->generateVerificationHash();
            }

            $grades = ($allGrades->get($report->student_id) ?? collect())
                ->sortBy(fn($sg) => $sg->subject?->name ?? '')
                ->values();

            $validScores = $grades->pluck('score')->filter(fn($score) => $score !== null);
            $averageScore = $validScores->isNotEmpty() ? round($validScores->average(), 2) : 0.0;
            $totalScore = $validScores->sum();

            $waliKelas = $report->waliKelas ?? $class->waliKelas;

            return [
                'report' => $report,
                'student' => $report->student,
                'schoolClass' => $class,
                'academicYear' => $activeYear,
                'tenant' => $tenant,
                'grades' => $grades,
                'extracurriculars' => $report->extracurriculars,
                'attendance' => [
                    'sick' => $report->sick_count,
                    'permission' => $report->permission_count,
                    'alpha' => $report->alpha_count,
                    'total' => $report->total_absence,
                ],
                'waliKelas' => $waliKelas,
                'headmaster' => $headmaster,
                'averageScore' => $averageScore,
                'totalScore' => $totalScore,
                'verificationUrl' => $report->verification_url,
            ];
        });

        // Urutkan siswa berdasarkan nama secara alfabetis
        return $cardItems->sortBy(fn($item) => $item['student']?->name ?? '')->values();
    }
}
