<?php

namespace App\Services;

use App\Models\AttendanceSchedule;
use App\Models\Tenant;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Mengambil mode presensi aktif tenant.
     */
    public function getAttendanceMode(Tenant $tenant): string
    {
        return $tenant->attendance_mode ?? 'formal_daily';
    }

    /**
     * Mengecek apakah tenant menggunakan Mode Presensi Sekolah Formal Harian.
     */
    public function isFormalDailyMode(Tenant $tenant): bool
    {
        return $this->getAttendanceMode($tenant) === 'formal_daily';
    }

    /**
     * Mengecek apakah tenant menggunakan Mode Presensi Non-Formal / Sesi KBM.
     */
    public function isNonFormalSessionMode(Tenant $tenant): bool
    {
        return $this->getAttendanceMode($tenant) === 'non_formal_session';
    }

    /**
     * Memvalidasi status kehadiran (present / late) berdasarkan mode presensi tenant.
     */
    public function validateAttendanceStatus(Tenant $tenant, string $checkInTime, string $dayName, $classSchedule = null): array
    {
        $mode = $this->getAttendanceMode($tenant);
        $checkIn = Carbon::parse($checkInTime);

        if ($mode === 'non_formal_session' && $classSchedule) {
            // Mode Non-Formal / Sesi KBM
            $sessionStart = Carbon::parse($classSchedule->start_time);
            $tolerance = $tenant->session_late_tolerance_minutes ?? 10;
            $lateLimit = (clone $sessionStart)->addMinutes($tolerance);

            $isLate = $checkIn->greaterThan($lateLimit);

            return [
                'mode' => 'non_formal_session',
                'status' => $isLate ? 'late' : 'present',
                'target_time' => $sessionStart->format('H:i'),
                'tolerance_minutes' => $tolerance,
                'is_late' => $isLate,
            ];
        } else {
            // Mode Formal Harian
            $schedule = AttendanceSchedule::where('tenant_id', $tenant->id)
                ->where('day_name', $dayName)
                ->first();

            $targetTime = $schedule ? $schedule->time_in : '07:00:00';
            $tolerance = $schedule ? $schedule->late_tolerance_minutes : 15;

            $scheduleStart = Carbon::parse($targetTime);
            $lateLimit = (clone $scheduleStart)->addMinutes($tolerance);

            $isLate = $checkIn->greaterThan($lateLimit);

            return [
                'mode' => 'formal_daily',
                'status' => $isLate ? 'late' : 'present',
                'target_time' => $scheduleStart->format('H:i'),
                'tolerance_minutes' => $tolerance,
                'is_late' => $isLate,
            ];
        }
    }
}
