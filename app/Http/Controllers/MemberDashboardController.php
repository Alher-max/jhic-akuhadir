<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberDashboardController extends Controller
{
    public function index()
    {
        // Protect: If user is not a student or member, redirect to admin dashboard
        if (!in_array(Auth::user()->role, ['student', 'member'])) {
            return redirect()->route('dashboard');
        }

        // Fetch today's attendance
        $todayAttendance = \App\Models\Attendance::where('user_id', Auth::id())
            ->where('date', date('Y-m-d'))
            ->first();

        // Check if there is an approved leave for today
        $todayLeave = \App\Models\LeaveRequest::where('user_id', Auth::id())
            ->where('status', 'approved')
            ->where('start_date', '<=', date('Y-m-d'))
            ->where('end_date', '>=', date('Y-m-d'))
            ->first();

        // Cari jadwal aktif untuk pengguna ini hari ini
        $now = \Carbon\Carbon::now();
        $todayDayOfWeek = $now->dayOfWeekIso; // 1 (Mon) - 7 (Sun)
        $todayDate = $now->format('Y-m-d');
        
        $currentSchedule = Auth::user()->schedules()
            ->where(function($query) use ($todayDayOfWeek, $todayDate) {
                $query->where(function($q) use ($todayDayOfWeek) {
                    $q->where('type', 'routine')->where('day_of_week', $todayDayOfWeek);
                })->orWhere(function($q) use ($todayDate) {
                    $q->where('type', 'non_routine')->where('specific_date', $todayDate);
                });
            })
            ->first();

        // MOCK JADWAL UNTUK PENGUJIAN LIVENESS HARI INI
        if (!$currentSchedule) {
            $currentSchedule = new \stdClass();
            $currentSchedule->name = "Jadwal Uji Coba Liveness (Mock)";
            $currentSchedule->start_time = "06:00:00";
            $currentSchedule->end_time = "23:59:00";
            $currentSchedule->grace_period_minutes = 60;
        }

        // Fetch weekly attendances
        $startOfWeek = clone $now;
        $startOfWeek->startOfWeek(\Carbon\Carbon::MONDAY);
        $endOfWeek = clone $now;
        $endOfWeek->endOfWeek(\Carbon\Carbon::SUNDAY);

        $weeklyAttendances = \App\Models\Attendance::where('user_id', Auth::id())
            ->whereBetween('date', [$startOfWeek->format('Y-m-d'), $endOfWeek->format('Y-m-d')])
            ->orderBy('date', 'desc')
            ->get();

        // Detect user type based on tenant's business category
        $userType = (Auth::user()->tenant->business_category === 'education') ? 'student' : 'employee';

        // Fetch weekly schedules (routine)
        $weeklySchedules = Auth::user()->schedules()
            ->where('type', 'routine')
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        // DUMMY DATA UNTUK MATA PELAJARAN MINGGUAN (Senin-Jumat)
        $dummyTimetable = [
            1 => [ // Senin
                ['jam' => 'Upacara', 'waktu' => '07:00 - 07:45', 'mapel' => 'Upacara Bendera', 'guru' => 'Semua Guru', 'ruang' => 'Lapangan', 'tipe' => 'upacara'],
                ['jam' => '1-2', 'waktu' => '07:45 - 09:15', 'mapel' => 'Matematika', 'guru' => 'Budi Santoso, S.Pd', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '09:15 - 09:45', 'mapel' => 'Istirahat Pertama', 'guru' => '-', 'ruang' => 'Kantin', 'tipe' => 'istirahat'],
                ['jam' => '3-4', 'waktu' => '09:45 - 11:15', 'mapel' => 'Bahasa Indonesia', 'guru' => 'Siti Aminah, M.Pd', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => '5-6', 'waktu' => '11:15 - 12:45', 'mapel' => 'Pendidikan Agama Islam', 'guru' => 'Ahmad Dahlan, S.Ag', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '12:45 - 13:15', 'mapel' => 'Ishoma', 'guru' => '-', 'ruang' => 'Masjid/Kantin', 'tipe' => 'istirahat'],
                ['jam' => '7-8', 'waktu' => '13:15 - 14:45', 'mapel' => 'Bahasa Inggris', 'guru' => 'John Doe, S.S', 'ruang' => 'Lab Bahasa', 'tipe' => 'pelajaran'],
            ],
            2 => [ // Selasa
                ['jam' => '1-2', 'waktu' => '07:00 - 08:30', 'mapel' => 'IPA Terpadu', 'guru' => 'Dr. Sains', 'ruang' => 'Lab IPA', 'tipe' => 'pelajaran'],
                ['jam' => '3-4', 'waktu' => '08:30 - 10:00', 'mapel' => 'IPS Terpadu', 'guru' => 'Sejarahwan, M.Hum', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '10:00 - 10:30', 'mapel' => 'Istirahat Pertama', 'guru' => '-', 'ruang' => 'Kantin', 'tipe' => 'istirahat'],
                ['jam' => '5-6', 'waktu' => '10:30 - 12:00', 'mapel' => 'Seni Budaya', 'guru' => 'Seniman, S.Sn', 'ruang' => 'Ruang Seni', 'tipe' => 'pelajaran'],
            ],
            3 => [ // Rabu
                ['jam' => '1-2', 'waktu' => '07:00 - 08:30', 'mapel' => 'Pendidikan Pancasila (PKn)', 'guru' => 'Budi Santoso, S.Pd', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => '3-4', 'waktu' => '08:30 - 10:00', 'mapel' => 'Prakarya', 'guru' => 'Kreatif, S.T', 'ruang' => 'Ruang Prakarya', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '10:00 - 10:30', 'mapel' => 'Istirahat', 'guru' => '-', 'ruang' => 'Kantin', 'tipe' => 'istirahat'],
                ['jam' => '5-7', 'waktu' => '10:30 - 12:45', 'mapel' => 'Pendidikan Jasmani (PJOK)', 'guru' => 'Atlet, S.Or', 'ruang' => 'Lapangan Olahraga', 'tipe' => 'pelajaran'],
            ],
            4 => [ // Kamis
                ['jam' => '1-2', 'waktu' => '07:00 - 08:30', 'mapel' => 'Matematika', 'guru' => 'Budi Santoso, S.Pd', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => '3-4', 'waktu' => '08:30 - 10:00', 'mapel' => 'Bimbingan Konseling', 'guru' => 'Psikolog, M.Psi', 'ruang' => 'Ruang BK', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '10:00 - 10:30', 'mapel' => 'Istirahat Pertama', 'guru' => '-', 'ruang' => 'Kantin', 'tipe' => 'istirahat'],
            ],
            5 => [ // Jumat
                ['jam' => 'Senam', 'waktu' => '07:00 - 07:45', 'mapel' => 'Senam Pagi Bersama', 'guru' => 'Semua Guru', 'ruang' => 'Lapangan Utama', 'tipe' => 'upacara'],
                ['jam' => '1-2', 'waktu' => '07:45 - 09:15', 'mapel' => 'Muatan Lokal (Bahasa Daerah)', 'guru' => 'Lokal, S.Pd', 'ruang' => 'Kelas 7A', 'tipe' => 'pelajaran'],
                ['jam' => 'Istirahat', 'waktu' => '09:15 - 09:45', 'mapel' => 'Istirahat', 'guru' => '-', 'ruang' => 'Kantin', 'tipe' => 'istirahat'],
                ['jam' => '3-4', 'waktu' => '09:45 - 11:15', 'mapel' => 'Informatika', 'guru' => 'Programmer, S.Kom', 'ruang' => 'Lab Komputer', 'tipe' => 'pelajaran'],
            ]
        ];

        $todayTimetable = $dummyTimetable[$todayDayOfWeek] ?? [];

        return view('member.dashboard', compact('todayAttendance', 'todayLeave', 'currentSchedule', 'weeklyAttendances', 'userType', 'weeklySchedules', 'dummyTimetable', 'todayTimetable', 'todayDayOfWeek'));
    }
}
