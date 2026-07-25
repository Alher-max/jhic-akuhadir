<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdminDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is staff_student (member), redirect to member dashboard
        if (Auth::user()->role === 'student') {
            return redirect()->route('member.dashboard');
        }

        $tenantId = Auth::user()->tenant_id;
        $today = date('Y-m-d');

        $totalSiswa = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->count();

        $opSiswaHadirTepat = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->where('status', 'present')
            ->whereHas('user', function ($q) { $q->where('role', 'student'); })
            ->count();
            
        $opSiswaTerlambat = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->where('status', 'late')
            ->whereHas('user', function ($q) { $q->where('role', 'student'); })
            ->count();

        $opSiswaIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->where('status', 'approved')
            ->whereHas('user', function ($q) { $q->where('role', 'student'); })
            ->count();

        $opSiswaAlpa = $totalSiswa - ($opSiswaHadirTepat + $opSiswaTerlambat + $opSiswaIzinSakit);
        if ($opSiswaAlpa < 0) $opSiswaAlpa = 0;

        $totalGuruStaff = \App\Models\User::where('tenant_id', $tenantId)
            ->whereNotIn('role', ['student', 'parent', 'kepala_sekolah'])
            ->count();

        $opGuruHadir = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->whereHas('user', function ($q) {
                $q->whereNotIn('role', ['student', 'parent', 'kepala_sekolah']);
            })
            ->count();

        $opGuruIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->whereHas('user', function ($q) {
                $q->whereNotIn('role', ['student', 'parent', 'kepala_sekolah']);
            })
            ->count();
            
        $totalRombel = \App\Models\SchoolClass::where('tenant_id', $tenantId)
            ->count();

        $opRombelKosong = \App\Models\SchoolClass::where('tenant_id', $tenantId)
            ->whereNull('wali_kelas_id')
            ->count();
            
        $opPendingInvitations = \Illuminate\Support\Facades\DB::table('invitations')
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->count();

        $sudahHadirHariIni = \App\Models\Attendance::where('tenant_id', $tenantId)
            ->whereDate('date', clone \Carbon\Carbon::today())
            ->count();

        $opSetting = \App\Models\AttendanceSetting::where('tenant_id', $tenantId)->first();
        $sysGpsActive = $opSetting ? ($opSetting->latitude && $opSetting->longitude) : false;
        $sysWifiActive = $opSetting ? $opSetting->method_wifi : false;
        $sysWaReady = true;

        // --- WALI KELAS SPECIFIC STATS & FILTER PREP ---
        $waliTotalSiswa = 0;
        $waliHadirHariIni = 0;
        $waliIzinSakit = 0;
        $waliBelumAbsen = 0;
        $waliClassName = 'Kelas Binaan';
        
        $pendingLeavesCount = 0;
        if (in_array(Auth::user()->role, ['kepala_sekolah', 'wali_kelas'])) {
            $pendingLeavesCount = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
                ->where('status', 'pending')
                ->count();
        }
        
        $availableClasses = collect();
        $isWaliKelas = Auth::user()->role === 'wali_kelas';

        if ($isWaliKelas) {
            $availableClasses = \App\Models\SchoolClass::where('wali_kelas_id', Auth::id())->get();
            $classIds = $availableClasses->pluck('id');
            
            $waliClassesCount = $availableClasses->count();
            if ($waliClassesCount === 1) {
                $waliClassName = 'Kelas ' . $availableClasses->first()->nama_kelas;
            } elseif ($waliClassesCount > 1) {
                $waliClassName = $waliClassesCount . ' Kelas Binaan (' . $availableClasses->pluck('nama_kelas')->implode(', ') . ')';
            } else {
                $waliClassName = 'Belum Ada Kelas Binaan';
            }

            $waliTotalSiswa = \App\Models\User::where('tenant_id', $tenantId)
                ->where('role', 'student')
                ->whereIn('class_id', $classIds)
                ->count();

            $waliHadirHariIni = \App\Models\Attendance::where('tenant_id', $tenantId)
                ->whereDate('date', $today)
                ->whereHas('user', function ($q) use ($classIds) {
                    $q->whereIn('class_id', $classIds);
                })
                ->whereIn('status', ['present', 'late'])
                ->count();

            $waliIzinSakit = \App\Models\LeaveRequest::where('tenant_id', $tenantId)
                ->whereDate('start_date', '<=', $today)
                ->whereDate('end_date', '>=', $today)
                ->whereHas('user', function ($q) use ($classIds) {
                    $q->whereIn('class_id', $classIds);
                })
                ->count();

            $waliBelumAbsen = $waliTotalSiswa - $waliHadirHariIni - $waliIzinSakit;
            if ($waliBelumAbsen < 0) $waliBelumAbsen = 0;
        } else {
            $availableClasses = \App\Models\SchoolClass::where('tenant_id', $tenantId)->get();
        }

        // Fetch today's detailed attendance list with filter & pagination
        $query = \App\Models\Attendance::with('user.schoolClass')
            ->where('tenant_id', $tenantId)
            ->where('date', $today);

        if ($isWaliKelas && $availableClasses->isNotEmpty()) {
            $classIds = $availableClasses->pluck('id');
            $query->whereHas('user', function ($q) use ($classIds) {
                $q->whereIn('class_id', $classIds);
            });
        }

        if (request()->filled('class_id')) {
            $query->whereHas('user', function ($q) {
                $q->where('class_id', request('class_id'));
            });
        }

        if (request()->filled('search')) {
            $search = request('search');
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $attendances = $query->orderByRaw('COALESCE(clock_in, updated_at) DESC')->paginate(10)->withQueryString();

        $tenant = \App\Models\Tenant::find($tenantId);

        $teachers = \App\Models\User::where('tenant_id', $tenantId)
            ->whereIn('role', ['wali_kelas', 'manager_teacher', 'guru', 'admin_dapodik'])
            ->where('is_active', true)
            ->get();

        $studentsForBantuAbsen = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->when($isWaliKelas && $availableClasses->isNotEmpty(), function($q) use ($availableClasses) {
                $q->whereIn('class_id', $availableClasses->pluck('id'));
            })
            ->with('schoolClass')
            ->orderBy('name')
            ->get();

        $viewName = in_array(Auth::user()->role, ['teacher', 'wali_kelas', 'guru', 'guru_mapel', 'manager_teacher'])
            ? 'teacher.dashboard'
            : 'dashboard';

        return view($viewName, compact(
            'totalSiswa', 'totalGuruStaff', 'totalRombel', 'sudahHadirHariIni', 
            'attendances', 'pendingLeavesCount', 'tenant', 
            'waliTotalSiswa', 'waliHadirHariIni', 'waliIzinSakit', 'waliBelumAbsen', 'waliClassName', 'availableClasses',
            'opSiswaHadirTepat', 'opSiswaTerlambat', 'opSiswaIzinSakit', 'opSiswaAlpa',
            'opGuruHadir', 'opGuruIzinSakit', 'opRombelKosong', 'opPendingInvitations',
            'sysGpsActive', 'sysWifiActive', 'sysWaReady', 'teachers', 'studentsForBantuAbsen'
        ));
    }

    public function storeManualAttendance(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:users,id',
            'status' => 'required|in:present,late',
            'notes' => 'nullable|string|max:255',
        ]);

        $tenantId = Auth::user()->tenant_id;
        $student = \App\Models\User::where('tenant_id', $tenantId)
            ->where('role', 'student')
            ->findOrFail($request->student_id);

        $today = \Carbon\Carbon::today()->format('Y-m-d');
        $now = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        $notes = $request->notes ?: 'Presensi manual dibantu oleh Guru / Wali Kelas';

        \App\Models\Attendance::updateOrCreate(
            [
                'user_id' => $student->id,
                'tenant_id' => $tenantId,
                'date' => $today,
            ],
            [
                'clock_in' => $now,
                'status' => $request->status,
                'notes' => $notes,
            ]
        );

        return redirect()->back()->with('success', "Presensi {$student->name} berhasil dicatat oleh Guru!");
    }

    public function updateBanner(Request $request)
    {
        $request->validate([
            'banner_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
            'banner_title' => 'nullable|string|max:255',
            'banner_description' => 'nullable|string|max:500',
            'banner_color' => 'nullable|string|in:red,blue,green,slate'
        ]);

        $tenant = \App\Models\Tenant::find(Auth::user()->tenant_id);
        
        if ($request->hasFile('banner_image')) {
            $path = $request->file('banner_image')->store('banners', 'public');
            
            // Delete old banner if exists
            if ($tenant->banner_path) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($tenant->banner_path);
            }
            
            $tenant->banner_path = $path;
        }

        if ($request->filled('banner_title')) {
            $tenant->banner_title = $request->banner_title;
        }
        
        if ($request->filled('banner_description')) {
            $tenant->banner_description = $request->banner_description;
        }

        if ($request->filled('banner_color')) {
            $tenant->banner_color = $request->banner_color;
        }

        $tenant->save();

        return redirect()->back()->with('success', 'Pengaturan Banner berhasil diperbarui.');
    }
}
