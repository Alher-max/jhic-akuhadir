<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\StudentReport;
use App\Models\SubjectGrade;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;

class ReportVerificationController extends Controller
{
    /**
     * Memverifikasi keabsahan dokumen rapor digital secara publik melalui hash QR code.
     *
     * @param string $hash
     * @return View|Response
     */
    public function verify(string $hash): View|Response
    {
        $report = StudentReport::withoutGlobalScope('tenant')
            ->where('verification_hash', $hash)
            ->with([
                'student',
                'schoolClass',
                'academicYear',
                'tenant',
                'waliKelas',
            ])
            ->first();

        if (!$report) {
            return response()->view('reports.verify', [
                'isValid' => false,
                'hash' => $hash,
            ], 404);
        }

        // Ambil data nilai untuk ringkasan verifikasi
        $grades = SubjectGrade::withoutGlobalScope('tenant')
            ->where('tenant_id', $report->tenant_id)
            ->where('academic_year_id', $report->academic_year_id)
            ->where('class_id', $report->class_id)
            ->where('student_id', $report->student_id)
            ->with('subject')
            ->get();

        $validScores = $grades->pluck('score')->filter(fn($score) => $score !== null);
        $averageScore = $validScores->isNotEmpty() ? round($validScores->average(), 2) : 0.0;

        return view('reports.verify', [
            'isValid' => true,
            'report' => $report,
            'student' => $report->student,
            'schoolClass' => $report->schoolClass,
            'academicYear' => $report->academicYear,
            'tenant' => $report->tenant,
            'waliKelas' => $report->waliKelas,
            'grades' => $grades,
            'averageScore' => $averageScore,
            'hash' => $hash,
        ]);
    }
}
