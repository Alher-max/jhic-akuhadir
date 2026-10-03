<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance;
use App\Models\InternshipPlacement;

class VocationalReportService
{
    /**
     * Menghitung skor kehadiran presensi industri siswa (0 - 100) berdasarkan log attendances.
     *
     * @param InternshipPlacement $placement
     * @return float
     */
    public function calculatePlacementAttendanceScore(InternshipPlacement $placement): float
    {
        $startDate = $placement->start_date->format('Y-m-d');
        $endDate = $placement->end_date->format('Y-m-d');

        $query = Attendance::withoutGlobalScope('tenant')
            ->where('tenant_id', $placement->tenant_id)
            ->where('user_id', $placement->student_id)
            ->whereBetween('date', [$startDate, $endDate]);

        // Jika penempatan memiliki tautan lokasi geofence industri mitra, filter khusus lokasi tersebut
        if (!empty($placement->industry_location_id)) {
            $query->where('location_id', $placement->industry_location_id);
        }

        $totalRecords = (clone $query)->count();

        if ($totalRecords === 0) {
            return 100.00;
        }

        // Hitung kehadiran (present dan late dianggap hadir)
        $presentCount = (clone $query)->whereIn('status', ['present', 'late'])->count();

        $score = ($presentCount / $totalRecords) * 100.00;

        return round($score, 2);
    }

    /**
     * Menghitung nilai akhir PKL terbobot:
     * Bobot standar: 50% Teknis + 30% Budaya Kerja (Softskill) + 20% Kehadiran Industri.
     *
     * @param float $technicalScore
     * @param float $softskillScore
     * @param float $attendanceScore
     * @return float
     */
    public function calculateFinalScore(float $technicalScore, float $softskillScore, float $attendanceScore): float
    {
        $final = ($technicalScore * 0.50) + ($softskillScore * 0.30) + ($attendanceScore * 0.20);
        return round($final, 2);
    }

    /**
     * Menentukan predikat kualitatif PKL berdasarkan nilai akhir:
     * - >= 85.00: "Sangat Baik"
     * - 75.00 - 84.99: "Baik"
     * - < 75.00: "Cukup"
     *
     * @param float $finalScore
     * @return string
     */
    public function determinePredicate(float $finalScore): string
    {
        if ($finalScore >= 85.00) {
            return 'Sangat Baik';
        }

        if ($finalScore >= 75.00) {
            return 'Baik';
        }

        return 'Cukup';
    }

    /**
     * Menentukan predikat kelulusan Uji Kompetensi Keahlian (UKK):
     * - >= 85.00: "Sangat Kompeten"
     * - >= 70.00: "Kompeten"
     * - < 70.00: "Belum Kompeten"
     *
     * @param float $finalScore
     * @return string
     */
    public function determineUKKPredicate(float $finalScore): string
    {
        if ($finalScore >= 85.00) {
            return 'Sangat Kompeten';
        }

        if ($finalScore >= 70.00) {
            return 'Kompeten';
        }

        return 'Belum Kompeten';
    }
}
