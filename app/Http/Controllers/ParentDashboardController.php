<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\ClassSchedule;
use App\Models\ActivitySchedule;
use App\Models\Attendance;
use Carbon\Carbon;

class ParentDashboardController extends Controller
{
    public function index()
    {
        $parent = Auth::user();

        $children = $parent->students()
            ->where('users.tenant_id', $parent->tenant_id)
            ->with(['schoolClass'])
            ->get()
            ->merge(
                $parent->children()
                    ->where('tenant_id', $parent->tenant_id)
                    ->with(['schoolClass'])
                    ->get()
            )
            ->unique('id')
            ->values();

        $today = Carbon::today();
        $startOfWeek = $today->copy()->startOfWeek(Carbon::MONDAY);
        $endOfWeek = $today->copy()->endOfWeek(Carbon::SUNDAY);
        $attendanceService = app(\App\Services\AttendanceService::class);
        $todayDayName = $attendanceService->getDayNameInIndonesian(Carbon::now());
        
        $childrenAttendances = [];
        foreach ($children as $child) {
            $attendance = Attendance::where('user_id', $child->id)
                ->where('tenant_id', $parent->tenant_id)
                ->whereDate('date', $today)
                ->first();
            $weeklyAttendances = Attendance::where('user_id', $child->id)
                ->where('tenant_id', $parent->tenant_id)
                ->whereBetween('date', [$startOfWeek->toDateString(), $endOfWeek->toDateString()])
                ->orderByDesc('date')
                ->get();

            // 1. KBM Schedules hari ini
            $kbmSchedules = collect();
            if ($child->class_id) {
                $kbmSchedules = ClassSchedule::with(['subject', 'teacher', 'schoolClass'])
                    ->where('tenant_id', $child->tenant_id)
                    ->where('class_id', $child->class_id)
                    ->where('day_name', $todayDayName)
                    ->orderBy('start_time', 'asc')
                    ->get();
            }

            // 2. Activity Schedules (Ekskul & Kegiatan) hari ini
            $activitySchedules = ActivitySchedule::with(['members'])
                ->where('tenant_id', $child->tenant_id)
                ->where('day_name', $todayDayName)
                ->get()
                ->filter(function ($act) use ($child) {
                    $scope = $act->target_scope ?? 'all';
                    if ($scope === 'all') {
                        return true;
                    }
                    if ($scope === 'class' && $child->class_id) {
                        $classIds = is_array($act->target_class_ids) ? $act->target_class_ids : [];
                        return in_array((string)$child->class_id, array_map('strval', $classIds));
                    }
                    if ($scope === 'members') {
                        return $act->members->contains('id', $child->id);
                    }
                    return false;
                });

            // 3. Merge KBM + Activity ke agenda hari ini
            $todayAgenda = collect();

            foreach ($kbmSchedules as $cs) {
                $todayAgenda->push((object)[
                    'type' => 'pelajaran',
                    'title' => $cs->subject->name ?? 'Mata Pelajaran',
                    'subtitle' => 'Guru: ' . ($cs->teacher->name ?? '-'),
                    'start_time' => $cs->start_time,
                    'end_time' => $cs->end_time,
                    'time_str' => Carbon::parse($cs->start_time)->format('H:i') . ' - ' . Carbon::parse($cs->end_time)->format('H:i'),
                    'badge' => 'KBM',
                    'badge_class' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                ]);
            }

            foreach ($activitySchedules as $act) {
                $badgeText = $act->target_scope === 'members' ? 'Ekskul' : 'Kegiatan';
                $todayAgenda->push((object)[
                    'type' => 'kegiatan',
                    'title' => $act->name,
                    'subtitle' => $act->target_scope === 'members' ? 'Ekskul / Anggota' : 'Kegiatan Sekolah',
                    'start_time' => $act->start_time,
                    'end_time' => $act->end_time,
                    'time_str' => Carbon::parse($act->start_time)->format('H:i') . ' - ' . Carbon::parse($act->end_time)->format('H:i'),
                    'badge' => $badgeText,
                    'badge_class' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                ]);
            }

            $sortedAgenda = $todayAgenda->sortBy('start_time')->values();

            $childrenAttendances[] = (object)[
                'child' => $child,
                'attendance' => $attendance,
                'weeklyAttendances' => $weeklyAttendances,
                'todayAgenda' => $sortedAgenda,
                'todayDayName' => $todayDayName,
                'announcements' => \App\Models\Announcement::where('school_class_id', $child->class_id)
                    ->whereIn('target_audience', ['parents', 'both'])
                    ->where(function ($query) {
                        $query->where('created_at', '>=', now()->subDays(7))
                              ->orWhere(function ($q) {
                                  $q->whereNull('expired_at')
                                    ->orWhere('expired_at', '>=', now());
                              });
                    })
                    ->latest()
                    ->limit(5)
                    ->get(),
            ];
        }

        return view('parent.dashboard', compact('parent', 'children', 'childrenAttendances', 'today', 'todayDayName'));
    }
}