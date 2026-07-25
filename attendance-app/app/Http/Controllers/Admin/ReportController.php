<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    private function getDateRange($period)
    {
        switch ($period) {
            case 'weekly':
                $startDate = Carbon::now()->subDays(6)->startOfDay(); // 7 days including today
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'semesterly':
                $month = date('n');
                if ($month <= 6) {
                    $startDate = Carbon::create(date('Y'), 1, 1)->startOfDay();
                    $endDate = Carbon::create(date('Y'), 6, 30)->endOfDay();
                } else {
                    $startDate = Carbon::create(date('Y'), 7, 1)->startOfDay();
                    $endDate = Carbon::create(date('Y'), 12, 31)->endOfDay();
                }
                break;
            case 'monthly':
            default:
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        return [$startDate, $endDate];
    }

    private function getTopRankings($role, $startDate, $endDate, $limit = 3)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $rankings = User::withoutGlobalScopes()->where('users.tenant_id', $tenantId)
            ->where('users.role', $role)
            ->where('users.is_active', true)
            ->join('attendances', 'users.id', '=', 'attendances.user_id')
            ->where('attendances.tenant_id', $tenantId)
            ->whereBetween('attendances.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendances.status', 'present')
            ->select(
                'users.id', 'users.name', 'users.master_photo',
                DB::raw('COUNT(attendances.id) as total_present'),
                DB::raw('AVG(TIME_TO_SEC(attendances.clock_in)) as avg_clock_in_sec')
            )
            ->groupBy('users.id', 'users.name', 'users.master_photo')
            ->orderBy('total_present', 'desc')
            ->orderBy('avg_clock_in_sec', 'asc')
            ->limit($limit)
            ->get();
            
        // Map average time string
        return $rankings->map(function($user) {
            $user->avg_clock_in = gmdate("H:i:s", (int)$user->avg_clock_in_sec);
            return $user;
        });
    }

    private function getTopClasses($startDate, $endDate, $limit = 3)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // Menghitung kehadiran kelas dengan rata-rata jam masuk
        $rankings = SchoolClass::withoutGlobalScopes()->where('school_classes.tenant_id', $tenantId)
            ->join('users', 'school_classes.id', '=', 'users.class_id')
            ->where('users.tenant_id', $tenantId)
            ->join('attendances', 'users.id', '=', 'attendances.user_id')
            ->where('attendances.tenant_id', $tenantId)
            ->where('users.role', 'student')
            ->whereBetween('attendances.date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->where('attendances.status', 'present')
            ->select(
                'school_classes.id', 'school_classes.nama_kelas as name',
                DB::raw('COUNT(attendances.id) as total_present'),
                DB::raw('COUNT(DISTINCT users.id) as total_students'),
                DB::raw('AVG(TIME_TO_SEC(attendances.clock_in)) as avg_clock_in_sec')
            )
            ->groupBy('school_classes.id', 'school_classes.nama_kelas')
            // Urutkan berdasarkan rata-rata per siswa jika memungkinkan, 
            // tapi sebagai pendekatan kita gunakan total present / students, atau total present utuh.
            // Requirement: "Rata-rata persentase kehadiran seluruh siswa di kelas tersebut".
            // Kita sorting by (total_present / total_students) DESC, lalu avg_clock_in_sec ASC
            ->orderBy(DB::raw('total_present / total_students'), 'desc')
            ->orderBy('avg_clock_in_sec', 'asc')
            ->limit($limit)
            ->get();
            
        return $rankings->map(function($cls) {
            $cls->avg_clock_in = gmdate("H:i:s", (int)$cls->avg_clock_in_sec);
            $cls->avg_present_per_student = round($cls->total_present / $cls->total_students, 1);
            return $cls;
        });
    }

    private function getGlobalSummary($startDate, $endDate)
    {
        $tenantId = Auth::user()->tenant_id;
        
        $attendances = Attendance::where('attendances.tenant_id', $tenantId)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');
            
        return [
            'present' => ($attendances['present'] ?? 0) + ($attendances['late'] ?? 0),
            'sick' => $attendances['sick'] ?? 0,
            'permission' => $attendances['permission'] ?? 0,
            'alpha' => $attendances['alpha'] ?? 0, // Jika ada status alpha yang tercatat secara eksplisit
            'total' => $attendances->sum()
        ];
    }

    public function index(Request $request)
    {
        $period = $request->input('period', 'monthly'); // default monthly
        
        list($startDate, $endDate) = $this->getDateRange($period);
        
        $topStudents = $this->getTopRankings('student', $startDate, $endDate);
        $topTeachers = $this->getTopRankings('teacher', $startDate, $endDate);
        $topStaffs = $this->getTopRankings('staff', $startDate, $endDate); // Jika tidak ada staff, bisa operator
        
        // Fallback operator if no staff
        if ($topStaffs->isEmpty()) {
            $topStaffs = $this->getTopRankings('operator', $startDate, $endDate);
        }

        $topClasses = $this->getTopClasses($startDate, $endDate);
        
        $globalSummary = $this->getGlobalSummary($startDate, $endDate);

        // Pertahankan getRecapData lama untuk tabel di bawah (bila masih dibutuhkan)
        $rekapSearch = $request->input('rekap_search');
        $selectedMonth = $startDate->format('Y-m');
        list($year, $month) = explode('-', $selectedMonth);
        $reports = $this->getRecapData($month, $year, null, false, $rekapSearch);

        return view('admin.reports.index', compact(
            'period', 'startDate', 'endDate', 
            'topStudents', 'topTeachers', 'topStaffs', 'topClasses',
            'globalSummary', 'reports', 'selectedMonth'
        ));
    }

    // === METODE LAMA DIPERTAHANKAN ===
    private function getRecapData($month, $year, $locationId = null, $isExport = false, $search = null)
    {
        $tenantId = Auth::user()->tenant_id;

        $query = User::with(['schedules', 'location'])->where('users.tenant_id', $tenantId)->where('users.role', 'student');
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        if ($isExport) {
            $members = $query->get();
        } else {
            $members = $query->paginate(15);
        }

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        if ($month == date('m') && $year == date('Y')) {
            $endDate = Carbon::now();
        }

        $reports = [];

        foreach ($members as $member) {
            $attendances = Attendance::where('user_id', $member->id)
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $present = $attendances->where('status', 'present')->count();
            $late = $attendances->where('status', 'late')->count();
            $sick = $attendances->where('status', 'sick')->count();
            $permission = $attendances->where('status', 'permission')->count();
            $duty = $attendances->where('status', 'duty_trip')->count();

            $alpha = 0;
            $totalWorkingDays = 0;
            
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayOfWeek = $date->format('N');
                $dateString = $date->format('Y-m-d');
                
                $hasSchedule = $member->schedules->first(function($s) use ($dayOfWeek, $dateString) {
                    return ($s->type === 'routine' && $s->day_of_week == $dayOfWeek) || 
                           ($s->type === 'non_routine' && $s->specific_date == $dateString);
                });
                
                if ($hasSchedule) {
                    $totalWorkingDays++;
                    $hasAttendanceRecord = $attendances->where('date', $dateString)->first();
                    if (!$hasAttendanceRecord) {
                        $alpha++;
                    }
                }
            }

            $denominator = $present + $late + $alpha;
            $disciplinePercentage = $denominator > 0 ? round(($present / $denominator) * 100, 1) : 0;

            $locationName = $member->location ? $member->location->name : 'Semua Kelas';

            $reports[] = [
                'name' => $member->name,
                'email' => $member->email,
                'location_name' => $locationName,
                'present' => $present,
                'late' => $late,
                'sick' => $sick,
                'permission' => $permission,
                'duty' => $duty,
                'alpha' => $alpha,
                'discipline_score' => $disciplinePercentage
            ];
        }

        $sortedReports = collect($reports)->sortByDesc('discipline_score')->values()->all();
        
        if ($isExport) {
            return $sortedReports;
        } else {
            $members->setCollection(collect($sortedReports));
            return $members;
        }
    }

    public function exportExcel(Request $request)
    {
        $selectedMonth = $request->input('month', date('Y-m'));
        list($year, $month) = explode('-', $selectedMonth);
        $reports = $this->getRecapData($month, $year, null, true);

        $filename = "Laporan_Kehadiran_".$selectedMonth.".csv";
        
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Nama Anggota', 'Email', 'Kelas', 'Hadir Tepat Waktu', 'Terlambat', 'Sakit', 'Izin', 'Tugas Luar', 'Alpha', 'Skor Kedisiplinan (%)');

        $callback = function() use($reports, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($reports as $r) {
                fputcsv($file, array(
                    $r['name'],
                    $r['email'],
                    $r['location_name'],
                    $r['present'],
                    $r['late'],
                    $r['sick'],
                    $r['permission'],
                    $r['duty'],
                    $r['alpha'],
                    $r['discipline_score']
                ));
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportPdf(Request $request)
    {
        $selectedMonth = $request->input('month', date('Y-m'));
        list($year, $month) = explode('-', $selectedMonth);
        $reports = $this->getRecapData($month, $year, null, true);
        
        $locationName = "Semua Kelas";

        return view('admin.reports.pdf', compact('reports', 'selectedMonth', 'locationName'));
    }
}
