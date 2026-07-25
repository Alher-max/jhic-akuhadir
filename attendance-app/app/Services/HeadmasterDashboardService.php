<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Invitation;
use App\Models\Schedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class HeadmasterDashboardService
{
    /**
     * Build all dashboard metrics and data for Headmaster.
     */
    public function getDashboardData(string $period = 'this_semester', ?int $tenantId = null): array
    {
        $tenantId = $tenantId ?? Auth::user()->tenant_id;
        $today = Carbon::today();

        // 1. Totals
        $totalSiswa = User::where('tenant_id', $tenantId)->where('role', 'student')->count();
        $totalGuru = User::where('tenant_id', $tenantId)->whereIn('role', ['teacher', 'wali_kelas'])->count();
        $totalStaf = User::where('tenant_id', $tenantId)->whereIn('role', ['operator', 'admin_dapodik'])->count();

        // 2. Start Date Calculation
        $startDate = match($period) {
            'today' => $today->copy(),
            'this_week' => $today->copy()->startOfWeek(),
            'this_month' => $today->copy()->startOfMonth(),
            'this_semester' => $today->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth(),
            default => $today->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth(),
        };

        // 3. Discipline Rates
        $studentAttendances = Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
            ->whereBetween('date', [$startDate, Carbon::now()])
            ->get();
        $studentDisciplineRate = $studentAttendances->count() > 0 
            ? round(($studentAttendances->where('status', 'present')->count() / $studentAttendances->count()) * 100) 
            : 0;

        $staffAttendances = Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->whereIn('role', ['teacher', 'wali_kelas', 'operator', 'admin_dapodik']); })
            ->whereBetween('date', [$startDate, Carbon::now()])
            ->get();
        $staffDisciplineRate = $staffAttendances->count() > 0 
            ? round(($staffAttendances->where('status', 'present')->count() / $staffAttendances->count()) * 100) 
            : 0;

        // 4. Live Snapshots
        $todayStudentAttendances = Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
            ->whereDate('date', $today)
            ->get();

        $siswaSnapshot = [
            'hadir' => $todayStudentAttendances->where('status', 'present')->count(),
            'terlambat' => $todayStudentAttendances->where('status', 'late')->count(),
            'izin' => $todayStudentAttendances->whereIn('status', ['leave', 'sick', 'excused'])->count(),
            'alpa' => $todayStudentAttendances->where('status', 'absent')->count(),
        ];

        $todayTeacherAttendances = Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->whereIn('role', ['teacher', 'wali_kelas']); })
            ->whereDate('date', $today)
            ->get();

        $guruSnapshot = [
            'hadir' => $todayTeacherAttendances->whereIn('status', ['present', 'late'])->count(),
            'absen' => $todayTeacherAttendances->whereIn('status', ['absent', 'leave', 'sick'])->count(),
            'belum_absen' => max(0, $totalGuru - $todayTeacherAttendances->count()),
        ];

        // 5. 7-Day Trend
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $today->copy()->subDays($i);
            $atts = Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', $d)
                ->get();
            $rate = $atts->count() > 0 ? round(($atts->where('status', 'present')->count() / $atts->count()) * 100) : 0;
            $trendData[] = [
                'day' => $d->format('D'),
                'rate' => $rate
            ];
        }

        // 6. Schedule Today
        $dayOfWeek = $today->dayOfWeekIso;
        $jadwalGuru = Schedule::where('tenant_id', $tenantId)
            ->where('day_of_week', $dayOfWeek)
            ->with(['teacher', 'schoolClass'])
            ->orderBy('start_time')
            ->get();

        // 7. Problematic Students
        $semesterStart = $today->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth();
        $problematicStudents = User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->withCount([
                'attendances as absent_count' => function($query) use ($semesterStart) {
                    $query->where('status', 'absent')->where('date', '>=', $semesterStart);
                }
            ])
            ->orderByDesc('absent_count')
            ->take(5)
            ->get();

        // 8. Invitations & Operators
        $invitations = Invitation::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();

        $operators = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['operator', 'admin_dapodik'])
            ->orderBy('name')
            ->get();

        return compact(
            'totalSiswa', 'totalGuru', 'totalStaf',
            'studentDisciplineRate', 'staffDisciplineRate',
            'period',
            'siswaSnapshot', 'guruSnapshot',
            'trendData',
            'jadwalGuru',
            'problematicStudents',
            'invitations',
            'operators'
        );
    }
}
