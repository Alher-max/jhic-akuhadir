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

        // Fetch today's attendance
        $todayAttendance = Attendance::where('user_id', $user->id)
            ->where('date', date('Y-m-d'))
            ->first();

        // Check if there is an approved leave for today
        $todayLeave = LeaveRequest::where('user_id', $user->id)
            ->where('status', 'approved')
            ->where('start_date', '<=', date('Y-m-d'))
            ->where('end_date', '>=', date('Y-m-d'))
            ->first();

        // Cari jadwal aktif untuk pengguna ini hari ini
        $now = Carbon::now();
        $todayDayOfWeek = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $todayDate = $now->format('Y-m-d');
        
        $currentSchedule = $user->schedules()
            ->where(function($query) use ($todayDayOfWeek, $todayDate) {
                $query->where(function($q) use ($todayDayOfWeek) {
                    $q->where('type', 'routine')->where('day_of_week', $todayDayOfWeek);
                })->orWhere(function($q) use ($todayDate) {
                    $q->where('type', 'non_routine')->where('specific_date', $todayDate);
                });
            })
            ->first();

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

        $weeklyTimetable = [];
        foreach ($dayNameMap as $num => $dName) {
            $schedulesForDay = $realClassSchedules->where('day_name', $dName)->values();
            $weeklyTimetable[$num] = $schedulesForDay->map(function ($cs) {
                return [
                    'jam' => $cs->period_number ? 'Jam ke-' . $cs->period_number : 'Sesi',
                    'waktu' => Carbon::parse($cs->start_time)->format('H:i') . ' - ' . Carbon::parse($cs->end_time)->format('H:i'),
                    'mapel' => $cs->subject->name ?? 'Mata Pelajaran',
                    'guru' => $cs->teacher->name ?? '-',
                    'ruang' => $cs->schoolClass->nama_kelas ?? '-',
                    'tipe' => 'pelajaran',
                ];
            })->toArray();
        }

        $todayTimetable = $weeklyTimetable[$todayDayOfWeek] ?? [];
        $dummyTimetable = $weeklyTimetable; // Dijaga untuk kompatibilitas variabel view

        return view('member.dashboard', compact(
            'todayAttendance',
            'todayLeave',
            'currentSchedule',
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

