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

        // Cari sesi KBM kelas siswa atau sesi pengajaran guru yang aktif di jam berjalan
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

        // Fallback: Jika tidak ada sesi di jam eksak, ambil sesi terdekat hari ini
        if (!$activeSession) {
            $activeSession = $query->orderBy('start_time')->first();
        }

        return $activeSession;
    }

    /**
     * Mengecek apakah pengguna sudah presensi pada sesi tertentu / hari ini.
     */
    public function hasAttendedSession(User $user, string $date, ?int $classScheduleId = null): bool
    {
        $query = Attendance::where('user_id', $user->id)
            ->where('date', $date);

        if ($classScheduleId) {
            $query->where('class_schedule_id', $classScheduleId);
        } else {
            $query->whereNull('class_schedule_id');
        }

        return $query->exists();
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
