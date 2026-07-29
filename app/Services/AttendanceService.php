<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceSchedule;
use App\Models\ClassSchedule;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;

class AttendanceService
{
    /**
     * Mengambil mode presensi aktif tenant (daily_arrival | session_based).
     */
    public function getAttendanceMode(Tenant $tenant): string
    {
        $mode = $tenant->attendance_mode ?? 'daily_arrival';
        if ($mode === 'formal_daily') return 'daily_arrival';
        if ($mode === 'non_formal_session') return 'session_based';
        return $mode;
    }

    /**
     * Mengecek apakah tenant menggunakan Mode Presensi Kedatangan Harian.
     */
    public function isDailyArrivalMode(Tenant $tenant): bool
    {
        return $this->getAttendanceMode($tenant) === 'daily_arrival';
    }

    /**
     * Mengecek apakah tenant menggunakan Mode Presensi Per Sesi / Jam Pembelajaran.
     */
    public function isSessionBasedMode(Tenant $tenant): bool
    {
        return $this->getAttendanceMode($tenant) === 'session_based';
    }

    /**
     * Alias untuk kompatibilitas.
     */
    public function isFormalDailyMode(Tenant $tenant): bool
    {
        return $this->isDailyArrivalMode($tenant);
    }

    /**
     * Alias untuk kompatibilitas.
     */
    public function isNonFormalSessionMode(Tenant $tenant): bool
    {
        return $this->isSessionBasedMode($tenant);
    }

    /**
     * Mengonversi nama hari Carbon ke Bahasa Indonesia.
     */
    public function getDayNameInIndonesian(Carbon $date): string
    {
        $days = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];
        return $days[(int)$date->format('N')] ?? 'Senin';
    }

    /**
     * Mencari Sesi KBM (ClassSchedule) aktif untuk pengguna pada waktu tertentu.
     */
    public function findActiveSession(User $user, ?Carbon $now = null): ?ClassSchedule
    {
        $now = $now ?? Carbon::now();
        $dayName = $this->getDayNameInIndonesian($now);
        $timeStr = $now->format('H:i:s');

        $query = ClassSchedule::where('tenant_id', $user->tenant_id)
            ->where('day_name', $dayName);

        if (!empty($user->class_id)) {
            $query->where('class_id', $user->class_id);
        } else {
            $query->where('teacher_id', $user->id);
        }

        $activeSession = (clone $query)
            ->whereTime('start_time', '<=', $timeStr)
            ->whereTime('end_time', '>=', $timeStr)
            ->first();

        if (!$activeSession) {
            $activeSession = $query->orderBy('start_time')->first();
        }

        return $activeSession;
    }

    /**
     * Mengecek apakah pengguna sudah presensi Hadir di Sekolah pada tanggal tertentu.
     */
    public function hasAttendedSchool(User $user, string $date): bool
    {
        return Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->where(function ($q) {
                $q->where('attendance_type', 'school')
                  ->orWhereNull('class_schedule_id');
            })
            ->exists();
    }

    /**
     * Mengecek apakah pengguna sudah presensi Hadir di Kelas untuk sesi KBM tertentu.
     */
    public function hasAttendedClassSession(User $user, string $date, int $classScheduleId): bool
    {
        return Attendance::where('user_id', $user->id)
            ->where('date', $date)
            ->where('class_schedule_id', $classScheduleId)
            ->exists();
    }

    /**
     * Wrapper kompatibilitas pengecekan presensi.
     */
    public function hasAttendedSession(User $user, string $date, ?int $classScheduleId = null): bool
    {
        if ($classScheduleId) {
            return $this->hasAttendedClassSession($user, $date, $classScheduleId);
        }
        return $this->hasAttendedSchool($user, $date);
    }

    /**
     * Memvalidasi status keterlambatan presensi sekolah berdasarkan konfigurasi operator.
     */
    public function validateSchoolAttendance(Tenant $tenant, string $checkInTime, string $dayName, $classSchedule = null): array
    {
        $mode = $this->getAttendanceMode($tenant);
        $checkIn = Carbon::parse($checkInTime);

        if ($mode === 'session_based' && $classSchedule) {
            $sessionStart = Carbon::parse($classSchedule->start_time);
            $tolerance = $tenant->session_late_tolerance_minutes ?? 10;
            $lateLimit = (clone $sessionStart)->addMinutes($tolerance);

            $isLate = $checkIn->greaterThan($lateLimit);

            return [
                'mode' => 'session_based',
                'status' => $isLate ? 'late' : 'present',
                'target_time' => $sessionStart->format('H:i'),
                'tolerance_minutes' => $tolerance,
                'is_late' => $isLate,
            ];
        } else {
            $schedule = AttendanceSchedule::where('tenant_id', $tenant->id)
                ->where('day_name', $dayName)
                ->first();

            $targetTime = $schedule ? $schedule->time_in : '07:00:00';
            $tolerance = $schedule ? $schedule->late_tolerance_minutes : 15;

            $scheduleStart = Carbon::parse($targetTime);
            $lateLimit = (clone $scheduleStart)->addMinutes($tolerance);

            $isLate = $checkIn->greaterThan($lateLimit);

            return [
                'mode' => 'daily_arrival',
                'status' => $isLate ? 'late' : 'present',
                'target_time' => $scheduleStart->format('H:i'),
                'tolerance_minutes' => $tolerance,
                'is_late' => $isLate,
            ];
        }
    }

    public function validateAttendanceStatus(Tenant $tenant, string $checkInTime, string $dayName, $classSchedule = null): array
    {
        return $this->validateSchoolAttendance($tenant, $checkInTime, $dayName, $classSchedule);
    }
}
