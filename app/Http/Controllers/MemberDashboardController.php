<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use App\Models\ClassSchedule;
use Carbon\Carbon;

class MemberDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is not a student or member, redirect to admin dashboard
        if (!in_array(Auth::user()->role, ['student', 'member'])) {
            return redirect()->route('dashboard');
        }

        $user = Auth::user();
        $user->load(['schoolClass.waliKelas', 'tenant']);

        // Cari jadwal aktif untuk pengguna ini hari ini (Timezone dinamis per tenant)
        $tz = $user->tenant->timezone ?? config('app.timezone', 'Asia/Jakarta');
        $now = Carbon::now($tz);
        $todayDayOfWeek = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $todayDate = $now->format('Y-m-d');
        $currentTimeStr = $now->format('H:i:s');

        // Fetch today's attendance (Timezone Tenant)
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $todayDate)
            ->first();

        // Check if there is an approved leave for today
        $todayLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', $todayDate)
            ->where('end_date', '>=', $todayDate)
            ->first();

        $attendanceService = app(\App\Services\AttendanceService::class);
        $dayNameIndo = $attendanceService->getDayNameInIndonesian($now);

        $currentSchedule = null;

        // 1. Cek Sesi KBM (ClassSchedule) jika siswa memiliki kelas
        if ($user->class_id) {
            $classSchedules = ClassSchedule::with(['subject'])
                ->where('tenant_id', $user->tenant_id)
                ->where('class_id', $user->class_id)
                ->where('day_name', $dayNameIndo)
                ->orderBy('start_time', 'asc')
                ->get();

            if ($classSchedules->isNotEmpty()) {
                // Cari sesi KBM yang sedang aktif (waktu saat ini berada di rentang start_time dan end_time)
                $activeSession = $classSchedules->first(function ($cs) use ($currentTimeStr, $tz) {
                    $start = Carbon::parse($cs->start_time, $tz)->format('H:i:s');
                    $end = Carbon::parse($cs->end_time, $tz)->format('H:i:s');
                    return $currentTimeStr >= $start && $currentTimeStr <= $end;
                });

                // Jika tidak ada sesi di menit ini, ambil sesi mendatang terdekat atau sesi pertama hari ini
                if (!$activeSession) {
                    $activeSession = $classSchedules->first(function ($cs) use ($currentTimeStr, $tz) {
                        return Carbon::parse($cs->end_time, $tz)->format('H:i:s') >= $currentTimeStr;
                    }) ?? $classSchedules->first();
                }

                if ($activeSession) {
                    $currentSchedule = (object)[
                        'id' => $activeSession->id,
                        'name' => 'Sesi KBM: ' . ($activeSession->subject->name ?? 'Mata Pelajaran'),
                        'start_time' => $activeSession->start_time,
                        'end_time' => $activeSession->end_time,
                        'grace_period_minutes' => $user->tenant->session_late_tolerance_minutes ?? 15,
                    ];
                }
            }
        }

        // 2. Jika tidak ada ClassSchedule, cek jadwal shift/rutin dari tabel schedules
        if (!$currentSchedule) {
            $currentSchedule = $user->schedules()
                ->where(function($query) use ($todayDayOfWeek, $todayDate) {
                    $query->where(function($q) use ($todayDayOfWeek) {
                        $q->where('type', 'routine')->where('day_of_week', $todayDayOfWeek);
                    })->orWhere(function($q) use ($todayDate) {
                        $q->where('type', 'non_routine')->where('specific_date', $todayDate);
                    });
                })
                ->first();
        }

        // 3. Cek Jadwal Sekolah Harian (AttendanceSchedule)
        if (!$currentSchedule) {
            $attendanceSchedule = \App\Models\AttendanceSchedule::where('tenant_id', $user->tenant_id)
                ->where('day_name', $dayNameIndo)
                ->where('is_active', true)
                ->first();

            if ($attendanceSchedule) {
                $currentSchedule = (object)[
                    'name' => 'Presensi Sekolah Harian',
                    'start_time' => $attendanceSchedule->time_in,
                    'end_time' => $attendanceSchedule->time_out,
                    'grace_period_minutes' => $attendanceSchedule->late_tolerance_minutes ?? 15,
                ];
            }
        }

        // 4. Fallback: Jam kerja/sekolah normal (06:00 - 23:59)
        if (!$currentSchedule && $now->hour >= 6 && $todayDayOfWeek <= 6) {
            $currentSchedule = (object)[
                'name' => 'Jadwal Presensi Sekolah',
                'start_time' => '06:00:00',
                'end_time' => '23:59:00',
                'grace_period_minutes' => 60,
            ];
        }

        // Calculation of Button States (hasClockedIn & canClockIn)
        $hasClockedIn = $todayAttendance && !empty($todayAttendance->clock_in_time);

        $canClockIn = false;
        if (!$hasClockedIn && $currentSchedule && !$todayLeave) {
            $startTimeStr = Carbon::parse($currentSchedule->start_time, $tz)->format('H:i:s');
            $endTimeStr = Carbon::parse($currentSchedule->end_time, $tz)->format('H:i:s');
            $earliestAllowed = Carbon::parse($currentSchedule->start_time, $tz)->subMinutes(60)->format('H:i:s');

            if ($currentTimeStr >= $earliestAllowed && $currentTimeStr <= $endTimeStr) {
                $canClockIn = true;
            }
        }

        // Fetch weekly attendances
        $startOfWeek = clone $now;
        $startOfWeek->startOfWeek(Carbon::MONDAY);
        $endOfWeek = clone $now;
        $endOfWeek->endOfWeek(Carbon::SUNDAY);

        $weeklyAttendances = Attendance::where('user_id', $user->id)
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        // Detect user type based on tenant's business category
        $userType = ($user->tenant->business_category === 'education') ? 'student' : 'employee';

        // Fetch weekly schedules (routine)
        $weeklySchedules = $user->schedules()
            ->where('type', 'routine')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // Ambil jadwal mata pelajaran riil dari database (ClassSchedule)
        $dayNameMap = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Minggu',
        ];

        $realClassSchedules = collect();
        if ($user->class_id) {
            $realClassSchedules = ClassSchedule::with(['subject', 'teacher', 'schoolClass'])
                ->where('tenant_id', $user->tenant_id)
                ->where('class_id', $user->class_id)
                ->orderBy('period_number', 'asc')
                ->orderBy('start_time', 'asc')
                ->get();
        }

        // Ambil kegiatan & ekskul (ActivitySchedule) yang relevan untuk siswa
        $relevantActivities = \App\Models\ActivitySchedule::with(['members'])
            ->where('tenant_id', $user->tenant_id)
            ->get()
            ->filter(function ($act) use ($user) {
                $scope = $act->target_scope ?? 'all';
                if ($scope === 'all') {
                    return true;
                }
                if ($scope === 'class' && $user->class_id) {
                    $classIds = is_array($act->target_class_ids) ? $act->target_class_ids : [];
                    return in_array((string)$user->class_id, array_map('strval', $classIds));
                }
                if ($scope === 'members') {
                    return $act->members->contains('id', $user->id);
                }
                return false;
            });

        $weeklyTimetable = [];
        foreach ($dayNameMap as $num => $dName) {
            $schedulesForDay = $realClassSchedules->where('day_name', $dName)->values();
            $kbmItems = $schedulesForDay->map(function ($cs) {
                return [
                    'jam' => $cs->period_number ? 'Jam ke-' . $cs->period_number : 'Sesi KBM',
                    'start_time' => $cs->start_time,
                    'waktu' => Carbon::parse($cs->start_time)->format('H:i') . ' - ' . Carbon::parse($cs->end_time)->format('H:i'),
                    'mapel' => $cs->subject->name ?? 'Mata Pelajaran',
                    'guru' => $cs->teacher->name ?? '-',
                    'ruang' => $cs->schoolClass->nama_kelas ?? '-',
                    'tipe' => 'pelajaran',
                    'badge' => 'KBM',
                    'badge_color' => 'indigo',
                ];
            });

            $actForDay = $relevantActivities->where('day_name', $dName)->values();
            $actItems = $actForDay->map(function ($act) {
                return [
                    'jam' => 'Kegiatan',
                    'start_time' => $act->start_time,
                    'waktu' => Carbon::parse($act->start_time)->format('H:i') . ' - ' . Carbon::parse($act->end_time)->format('H:i'),
                    'mapel' => $act->name,
                    'guru' => 'Toleransi ' . $act->late_tolerance_minutes . 'm',
                    'ruang' => $act->target_scope === 'members' ? 'Ekskul' : 'Kegiatan Sekolah',
                    'tipe' => 'kegiatan',
                    'badge' => $act->target_scope === 'members' ? 'Ekskul' : 'Kegiatan',
                    'badge_color' => 'emerald',
                ];
            });

            $weeklyTimetable[$num] = $kbmItems->concat($actItems)->sortBy(function ($item) {
                return $item['start_time'];
            })->values()->toArray();
        }

        $todayTimetable = $weeklyTimetable[$todayDayOfWeek] ?? [];
        $dummyTimetable = $weeklyTimetable; // Dijaga untuk kompatibilitas variabel view

        return view('member.dashboard', compact(
            'todayAttendance',
            'todayLeave',
            'currentSchedule',
            'hasClockedIn',
            'canClockIn',
            'weeklyAttendances',
            'userType',
            'weeklySchedules',
            'weeklyTimetable',
            'dummyTimetable',
            'todayTimetable',
            'todayDayOfWeek'
        ));
    }
}

