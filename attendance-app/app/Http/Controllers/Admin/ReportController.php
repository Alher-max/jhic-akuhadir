<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
{
    private function getRecapData($month, $year, $locationId = null)
    {
        $tenantId = Auth::user()->tenant_id;

        // Get all members for this tenant
        $query = User::with(['schedules', 'location'])->where('tenant_id', $tenantId)->where('role', 'staff_student');
        if ($locationId) {
            $query->where('location_id', $locationId);
        }
        $members = $query->get();

        $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
        
        // If the requested month is the current month, only calculate up to today
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

            // Calculate Alpha
            // Alpha is when they are scheduled to work but have no attendance record
            $alpha = 0;
            $totalWorkingDays = 0;
            
            // For each day in the date range
            for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
                $dayOfWeek = $date->format('N');
                $dateString = $date->format('Y-m-d');
                
                // Check if user has schedule this day
                $hasSchedule = $member->schedules->first(function($s) use ($dayOfWeek, $dateString) {
                    return ($s->type === 'routine' && $s->day_of_week == $dayOfWeek) || 
                           ($s->type === 'non_routine' && $s->specific_date == $dateString);
                });
                
                if ($hasSchedule) {
                    $totalWorkingDays++;
                    // Did they have any attendance record on this day?
                    $hasAttendanceRecord = $attendances->where('date', $dateString)->first();
                    if (!$hasAttendanceRecord) {
                        $alpha++;
                    }
                }
            }

            // Calculate Discipline Percentage
            // Discipline = (Present) / (Present + Late + Alpha)
            $denominator = $present + $late + $alpha;
            $disciplinePercentage = $denominator > 0 ? round(($present / $denominator) * 100, 1) : 0;

            $locationName = $member->location ? $member->location->name : 'Kantor Pusat / Utama';

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

        return $reports;
    }

    public function index(Request $request)
    {
        $selectedMonth = $request->input('month', date('Y-m')); // Format: YYYY-MM
        list($year, $month) = explode('-', $selectedMonth);
        $locationId = $request->input('location_id');

        $reports = $this->getRecapData($month, $year, $locationId);
        $locations = Auth::user()->tenant->locations;

        return view('admin.reports.index', compact('reports', 'selectedMonth', 'locations', 'locationId'));
    }

    public function exportExcel(Request $request)
    {
        $selectedMonth = $request->input('month', date('Y-m'));
        list($year, $month) = explode('-', $selectedMonth);
        $locationId = $request->input('location_id');
        $reports = $this->getRecapData($month, $year, $locationId);

        $filename = "Laporan_Kehadiran_".$selectedMonth.".csv";
        
        $headers = array(
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$filename",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        );

        $columns = array('Nama Anggota', 'Email', 'Lokasi / Cabang', 'Hadir Tepat Waktu', 'Terlambat', 'Sakit', 'Izin', 'Tugas Luar', 'Alpha', 'Skor Kedisiplinan (%)');

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
        $locationId = $request->input('location_id');
        $reports = $this->getRecapData($month, $year, $locationId);
        
        $locationName = "Semua Cabang / Lokasi";
        if ($locationId) {
            $location = \App\Models\Location::find($locationId);
            $locationName = $location ? $location->name : "Semua Cabang / Lokasi";
        }

        return view('admin.reports.pdf', compact('reports', 'selectedMonth', 'locationName'));
    }
}
