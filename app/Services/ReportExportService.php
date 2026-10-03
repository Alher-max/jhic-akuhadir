<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AcademicYear;
use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\StudentReport;
use App\Models\Subject;
use App\Models\SubjectGrade;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportExportService
{
    public function __construct(
        protected HomeroomReportService $homeroomReportService
    ) {}

    /**
     * Mengalirkan CSV template impor nilai e-Rapor SP Kemendikbudristek per rombel & mapel.
     * Format Header: No;NISN;NIS;Nama Siswa;Nilai Akhir;Capaian Tertinggi;Capaian Terendah
     */
    public function streamSubjectGradeForERapor(SchoolClass $class, Subject $subject, AcademicYear $academicYear): StreamedResponse
    {
        $sanitizedYear = str_replace(['/', '\\'], '-', $academicYear->name);
        $sanitizedClass = str_replace([' ', '/', '\\'], '_', $class->nama_kelas);
        $sanitizedSubject = str_replace([' ', '/', '\\'], '_', $subject->code ?? $subject->name);
        $filename = "e-Rapor_{$sanitizedClass}_{$sanitizedSubject}_Sem{$academicYear->semester}_{$sanitizedYear}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $tenantId = (int) $class->tenant_id;
        $students = $class->students()->orderBy('name')->get();

        $grades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $academicYear->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->get()
            ->keyBy('student_id');

        $callback = function () use ($students, $grades) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM untuk kompatibilitas Microsoft Excel
            fwrite($handle, "\xEF\xBB\xBF");

            // Header e-Rapor SP
            fputcsv($handle, [
                'No',
                'NISN',
                'NIS',
                'Nama Siswa',
                'Nilai Akhir',
                'Capaian Tertinggi',
                'Capaian Terendah',
            ], ';');

            foreach ($students as $index => $student) {
                $grade = $grades->get($student->id);
                $score = $grade && $grade->score !== null ? number_format((float) $grade->score, 0, '', '') : '';

                fputcsv($handle, [
                    $index + 1,
                    $student->nisn ?? '-',
                    $student->nis ?? '-',
                    $student->name,
                    $score,
                    $grade?->highest_achievement ?? '',
                    $grade?->lowest_achievement ?? '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mengalirkan CSV template impor nilai RDM Kemenag (Rapor Digital Madrasah).
     * Format Header: NO;NISN;NAMA SISWA;NILAI_PENGETAHUAN;DESKRIPSI_CAPAIAN
     */
    public function streamRDMExport(SchoolClass $class, Subject $subject, AcademicYear $academicYear): StreamedResponse
    {
        $sanitizedYear = str_replace(['/', '\\'], '-', $academicYear->name);
        $sanitizedClass = str_replace([' ', '/', '\\'], '_', $class->nama_kelas);
        $sanitizedSubject = str_replace([' ', '/', '\\'], '_', $subject->code ?? $subject->name);
        $filename = "RDM_{$sanitizedClass}_{$sanitizedSubject}_Sem{$academicYear->semester}_{$sanitizedYear}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $tenantId = (int) $class->tenant_id;
        $students = $class->students()->orderBy('name')->get();

        $grades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->where('academic_year_id', $academicYear->id)
            ->where('class_id', $class->id)
            ->where('subject_id', $subject->id)
            ->get()
            ->keyBy('student_id');

        $callback = function () use ($students, $grades) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // Header RDM Kemenag
            fputcsv($handle, [
                'NO',
                'NISN',
                'NAMA SISWA',
                'NILAI_PENGETAHUAN',
                'DESKRIPSI_CAPAIAN',
            ], ';');

            foreach ($students as $index => $student) {
                $grade = $grades->get($student->id);
                $score = $grade && $grade->score !== null ? number_format((float) $grade->score, 0, '', '') : '';

                $descriptions = [];
                if (!empty($grade?->highest_achievement)) {
                    $descriptions[] = 'Tercapai optimal: ' . $grade->highest_achievement;
                }
                if (!empty($grade?->lowest_achievement)) {
                    $descriptions[] = 'Perlu peningkatan: ' . $grade->lowest_achievement;
                }
                $combinedDescription = implode('; ', $descriptions);

                fputcsv($handle, [
                    $index + 1,
                    $student->nisn ?? '-',
                    $student->name,
                    $score,
                    $combinedDescription,
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Mengalirkan CSV Leger Lengkap & Rekap Nilai Dapodik per rombel kelas.
     * Format Header: No;NISN;NIS;Nama Siswa;Jenis Kelamin;[Mapel 1];[Mapel 2];...;Rata-rata;Sakit;Izin;Alpa;Catatan Wali Kelas
     */
    public function streamClassLeger(SchoolClass $class, AcademicYear $academicYear): StreamedResponse
    {
        $sanitizedYear = str_replace(['/', '\\'], '-', $academicYear->name);
        $sanitizedClass = str_replace([' ', '/', '\\'], '_', $class->nama_kelas);
        $filename = "Leger_Dapodik_{$sanitizedClass}_Sem{$academicYear->semester}_{$sanitizedYear}.csv";

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        // Ambil data matriks kompilasi via HomeroomReportService
        $matrixData = $this->homeroomReportService->buildLegerMatrix($class, $academicYear);
        $students = $matrixData['students'];
        $subjects = $matrixData['subjects'];
        $matrix = $matrixData['matrix'];
        $averages = $matrixData['averages'];
        $reports = $matrixData['reports'];

        $callback = function () use ($students, $subjects, $matrix, $averages, $reports) {
            $handle = fopen('php://output', 'w');
            if ($handle === false) {
                return;
            }

            // UTF-8 BOM
            fwrite($handle, "\xEF\xBB\xBF");

            // Bangun kolom dinamis
            $headerColumns = [
                'No',
                'NISN',
                'NIS',
                'Nama Siswa',
                'Jenis Kelamin',
            ];

            foreach ($subjects as $subject) {
                $headerColumns[] = $subject->name;
            }

            $headerColumns[] = 'Rata-rata';
            $headerColumns[] = 'Sakit';
            $headerColumns[] = 'Izin';
            $headerColumns[] = 'Alpa';
            $headerColumns[] = 'Catatan Wali Kelas';

            fputcsv($handle, $headerColumns, ';');

            foreach ($students as $index => $student) {
                $report = $reports->get($student->id);
                $avg = $averages[$student->id] ?? 0.0;

                $genderFormatted = match (strtolower((string) $student->gender)) {
                    'male', 'l', 'laki-laki' => 'L',
                    'female', 'p', 'perempuan' => 'P',
                    default => $student->gender ?? '-',
                };

                $row = [
                    $index + 1,
                    $student->nisn ?? '-',
                    $student->nis ?? '-',
                    $student->name,
                    $genderFormatted,
                ];

                // Nilai tiap mapel
                foreach ($subjects as $subject) {
                    $score = $matrix[$student->id][$subject->id] ?? null;
                    $row[] = $score !== null ? number_format((float) $score, 1, '.', '') : '-';
                }

                $row[] = number_format((float) $avg, 2, '.', '');
                $row[] = $report ? (string) $report->sick_count : '0';
                $row[] = $report ? (string) $report->permission_count : '0';
                $row[] = $report ? (string) $report->alpha_count : '0';
                $row[] = $report?->homeroom_notes ?? '';

                fputcsv($handle, $row, ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}
