<?php

namespace App\Http\Controllers;

use App\Models\AttendanceSchedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AttendanceScheduleController extends Controller
{
    /**
     * Menampilkan dan menginisialisasi jam operasional presensi 7 hari kerja.
     * Akses eksklusif untuk Operator / Admin Sekolah.
     */
    public function index()
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $roleLower = strtolower($user->role ?? '');
        $allowedRoles = ['operator', 'admin_dapodik', 'admin', 'super_admin'];
        if (!in_array($roleLower, $allowedRoles)) {
            abort(403, 'Akses tidak diizinkan. Pengaturan Jam & Mode Presensi hanya dapat diakses melalui Dasbor Operator Sekolah.');
        }

        $days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];

        // Auto-seed default 7 hari jika belum ada data untuk tenant ini
        foreach ($days as $day) {
            AttendanceSchedule::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'day_name' => $day,
                ],
                [
                    'time_in' => '07:00:00',
                    'late_tolerance_minutes' => 15,
                    'time_out' => in_array($day, ['Sabtu', 'Minggu']) ? '12:00:00' : '14:00:00',
                    'is_active' => !in_array($day, ['Sabtu', 'Minggu']),
                ]
            );
        }

        $tenant = \App\Models\Tenant::find($tenantId);

        $schedules = AttendanceSchedule::where('tenant_id', $tenantId)
            ->get()
            ->sortBy(function ($item) use ($days) {
                return array_search($item->day_name, $days);
            })
            ->values();

        return view('schedules.attendance', compact('schedules', 'days', 'tenant'));
    }

    /**
     * Memperbarui batch jam operasional presensi & mode presensi operator.
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        $tenantId = $user->tenant_id;

        $roleLower = strtolower($user->role ?? '');
        $allowedRoles = ['operator', 'admin_dapodik', 'admin', 'super_admin'];
        if (!in_array($roleLower, $allowedRoles)) {
            abort(403, 'Akses tidak diizinkan. Pengaturan Jam & Mode Presensi hanya dapat dikelola oleh Operator Sekolah.');
        }

        $request->validate([
            'attendance_mode' => 'nullable|in:daily_arrival,session_based,formal_daily,non_formal_session',
            'session_late_tolerance_minutes' => 'nullable|integer|min:0|max:180',
            'schedules' => 'required|array',
            'schedules.*.id' => 'required|exists:attendance_schedules,id',
            'schedules.*.time_in' => 'required',
            'schedules.*.time_out' => 'required',
            'schedules.*.late_tolerance_minutes' => 'required|integer|min:0|max:180',
        ]);

        $modeInput = $request->input('attendance_mode', 'daily_arrival');
        if ($modeInput === 'formal_daily') $modeInput = 'daily_arrival';
        if ($modeInput === 'non_formal_session') $modeInput = 'session_based';

        $tenant = \App\Models\Tenant::find($tenantId);
        if ($tenant) {
            $tenant->update([
                'attendance_mode' => $modeInput,
                'session_late_tolerance_minutes' => $request->input('session_late_tolerance_minutes', 10),
            ]);
        }

        foreach ($request->schedules as $scheduleData) {
            $schedule = AttendanceSchedule::where('tenant_id', $tenantId)->find($scheduleData['id']);
            if ($schedule) {
                $schedule->update([
                    'time_in' => $scheduleData['time_in'],
                    'time_out' => $scheduleData['time_out'],
                    'late_tolerance_minutes' => $scheduleData['late_tolerance_minutes'],
                    'is_active' => isset($scheduleData['is_active']) && $scheduleData['is_active'] == '1',
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pengaturan jam & mode presensi operator berhasil disimpan.');
    }
}
