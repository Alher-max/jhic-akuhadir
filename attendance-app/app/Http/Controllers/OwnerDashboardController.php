<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class OwnerDashboardController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = Auth::user()->tenant_id;
        
        // 1. METRIK UTAMA (TOTALS)
        $totalSiswa = User::where('tenant_id', $tenantId)->where('role', 'student')->count();
        $totalGuru = User::where('tenant_id', $tenantId)->where('role', 'wali_kelas')->count();
        $totalStaf = User::where('tenant_id', $tenantId)->where('role', 'admin_dapodik')->count();
        
        // FILTER PERIODE
        $period = $request->get('period', 'this_semester');
        $today = \Carbon\Carbon::today();
        
        $startDate = match($period) {
            'today' => $today->copy(),
            'this_week' => $today->copy()->startOfWeek(),
            'this_month' => $today->copy()->startOfMonth(),
            'this_semester' => $today->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth(),
            default => $today->copy()->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth(),
        };

        // KEDISIPLINAN (Present / Total Attendances)
        // Siswa
        $studentAttendances = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
            ->whereBetween('date', [$startDate, \Carbon\Carbon::now()])
            ->get();
        $studentDisciplineRate = $studentAttendances->count() > 0 
            ? round(($studentAttendances->where('status', 'present')->count() / $studentAttendances->count()) * 100) 
            : 0;

        // Guru & Staf
        $staffAttendances = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->whereIn('role', ['wali_kelas', 'admin_dapodik']); })
            ->whereBetween('date', [$startDate, \Carbon\Carbon::now()])
            ->get();
        $staffDisciplineRate = $staffAttendances->count() > 0 
            ? round(($staffAttendances->where('status', 'present')->count() / $staffAttendances->count()) * 100) 
            : 0;

        // 2. LIVE SNAPSHOT HARI INI
        $todayStudentAttendances = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->where('role', 'student'); })
            ->whereDate('date', $today)
            ->get();
        
        $siswaSnapshot = [
            'hadir' => $todayStudentAttendances->where('status', 'present')->count(),
            'terlambat' => $todayStudentAttendances->where('status', 'late')->count(),
            'izin' => $todayStudentAttendances->whereIn('status', ['leave', 'sick', 'excused'])->count(),
            'alpa' => $todayStudentAttendances->where('status', 'absent')->count(),
        ];

        // Hitung Guru Sedang Mengajar vs Absen/Izin (Sederhana: dari attendance hari ini)
        $todayTeacherAttendances = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereHas('user', function($q) { $q->where('role', 'wali_kelas'); })
            ->whereDate('date', $today)
            ->get();
            
        $guruSnapshot = [
            'hadir' => $todayTeacherAttendances->whereIn('status', ['present', 'late'])->count(),
            'absen' => $todayTeacherAttendances->whereIn('status', ['absent', 'leave', 'sick'])->count(),
            // Jika ada guru yg belum absen sama sekali (Total Guru - yang sudah record)
            'belum_absen' => max(0, $totalGuru - $todayTeacherAttendances->count()),
        ];

        // GRAFIK TREN (Data dummy per hari untuk disederhanakan, idealnya query per hari)
        // Kita hitung 7 hari terakhir
        $trendData = [];
        for ($i = 6; $i >= 0; $i--) {
            $d = $today->copy()->subDays($i);
            $atts = \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', $d)
                ->get();
            
            $rate = $atts->count() > 0 ? round(($atts->where('status', 'present')->count() / $atts->count()) * 100) : 0;
            $trendData[] = [
                'day' => $d->format('D'),
                'rate' => $rate
            ];
        }

        // 3. JADWAL MENGAJAR HARI INI
        $dayOfWeek = $today->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $jadwalGuru = \App\Models\Schedule::where('tenant_id', $tenantId)
            ->where('day_of_week', $dayOfWeek)
            ->with(['teacher', 'schoolClass'])
            ->orderBy('start_time')
            ->get();

        // 4. TOP INDISIPLINER (Semester Ini)
        $semesterStart = $today->copy()->month >= 7 ? $today->copy()->month(7)->startOfMonth() : $today->copy()->month(1)->startOfMonth();
        
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

        // 5. DAFTAR UNDANGAN & OPERATOR SEKOLAH
        $invitations = \App\Models\Invitation::where('tenant_id', $tenantId)
            ->orderByDesc('created_at')
            ->get();

        $operators = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['operator', 'admin_dapodik'])
            ->orderBy('name')
            ->get();

        return view('owner.dashboard', compact(
            'totalSiswa', 'totalGuru', 'totalStaf',
            'studentDisciplineRate', 'staffDisciplineRate',
            'period',
            'siswaSnapshot', 'guruSnapshot',
            'trendData',
            'jadwalGuru',
            'problematicStudents',
            'invitations',
            'operators'
        ));
    }

    // PENCARIAN CEPAT API
    public function searchStudent(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $students = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'student')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('nisn', 'like', "%{$query}%");
            })
            ->with('schoolClass')
            ->take(10)
            ->get();

        return response()->json($students);
    }

    public function searchTeacher(Request $request)
    {
        $query = $request->get('q');
        if (!$query) return response()->json([]);

        $teachers = User::where('tenant_id', Auth::user()->tenant_id)
            ->whereIn('role', ['wali_kelas', 'admin_dapodik'])
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('email', 'like', "%{$query}%");
            })
            ->take(10)
            ->get();

        return response()->json($teachers);
    }

    public function inviteSuperAdmin(Request $request)
    {
        $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
        ]);

        \App\Models\Invitation::create([
            'tenant_id' => Auth::user()->tenant_id,
            'email' => $request->email,
            'role' => 'admin_dapodik',
            'token' => \Illuminate\Support\Str::random(40),
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Undangan Operator Sekolah berhasil dibuat. Silakan bagikan tautan kepada yang bersangkutan.');
    }

    public function toggleSuperAdmin(\Illuminate\Http\Request $request, $id)
    {
        $request->validate([
            'password' => ['required', 'string'],
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password Kepala Sekolah tidak valid. Perubahan status dibatalkan!');
        }

        $admin = User::where('tenant_id', Auth::user()->tenant_id)
            ->where('role', 'admin_dapodik')
            ->findOrFail($id);

        $admin->is_active = !$admin->is_active;
        $admin->save();

        return redirect()->back()->with('success', 'Status Operator Sekolah berhasil diubah.');
    }
}
