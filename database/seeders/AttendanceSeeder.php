<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class AttendanceSeeder extends Seeder
{
    public function run()
    {
        // 1. Bersihkan tabel attendance lama agar tidak terjadi duplikasi data yang menumpuk
        Attendance::query()->delete();

        // Ambil ID tenant dari user pertama
        $tenantId = 1; 
        $firstUser = User::first();
        if ($firstUser) $tenantId = $firstUser->tenant_id;

        // Role yang ingin diberi data presensi
        $roles = ['student', 'teacher', 'staff'];
        $allAttendances = [];

        // Rentang Waktu: 4 bulan terakhir sampai hari ini
        $startDate = Carbon::now()->subMonths(4)->startOfDay();
        $endDate = Carbon::now()->endOfDay();
        
        // Filter hanya mengambil hari kerja (Senin - Jumat)
        $period = CarbonPeriod::create($startDate, '1 day', $endDate);
        $workdays = [];
        foreach ($period as $date) {
            if ($date->isWeekday()) {
                $workdays[] = $date->format('Y-m-d');
            }
        }

        foreach ($roles as $role) {
            // Ambil pengguna berdasarkan role. 
            // Apabila staff kosong, ambil dari role operator (karena mungkin sistem tidak pakai role staff)
            $users = User::where('role', $role)->get();
            if ($users->isEmpty() && $role === 'staff') {
                $users = User::whereIn('role', ['operator', 'admin_dapodik'])->get();
            }

            if ($users->isEmpty()) continue;
            
            // Tentukan kandidat Juara 1, 2, dan 3 (Ambil 3 siswa/guru pertama)
            $juara1 = $users[0] ?? null;
            $juara2 = $users[1] ?? null;
            $juara3 = $users[2] ?? null;

            foreach ($users as $index => $user) {
                // Tentukan karakter/behavior dari masing-masing user
                $behavior = 'reguler';
                if ($user->id === ($juara1->id ?? null)) $behavior = 'juara1';
                else if ($user->id === ($juara2->id ?? null)) $behavior = 'juara2';
                else if ($user->id === ($juara3->id ?? null)) $behavior = 'juara3';
                else if ($index % 5 === 0) $behavior = 'pemalas';

                foreach ($workdays as $dateString) {
                    $status = 'present';
                    $clockIn = null;
                    $clockOut = null;
                    
                    if ($behavior === 'juara1') {
                        // Juara 1: Selalu hadir tepat waktu. Paling rajin dan sangat pagi (06:30 - 06:40)
                        $clockIn = Carbon::parse($dateString)->setHour(6)->setMinute(rand(30, 40))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                    } else if ($behavior === 'juara2') {
                        // Juara 2: Selalu hadir tepat waktu. Agak lebih siang dari Juara 1 (06:45 - 06:55)
                        $clockIn = Carbon::parse($dateString)->setHour(6)->setMinute(rand(45, 55))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                    } else if ($behavior === 'juara3') {
                        // Juara 3: Selalu hadir tepat waktu. Lebih mepet bel masuk (06:56 - 07:05)
                        $min = rand(56, 65);
                        if ($min >= 60) {
                            $h = 7;
                            $m = $min - 60;
                        } else {
                            $h = 6;
                            $m = $min;
                        }
                        $clockIn = Carbon::parse($dateString)->setHour($h)->setMinute($m)->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                    } else if ($behavior === 'pemalas') {
                        // Pemalas: Banyak Alpha, Izin, Sakit, atau Terlambat (di atas jam 07:15)
                        $rand = rand(1, 100);
                        if ($rand <= 20) {
                            $status = 'late';
                            $clockIn = Carbon::parse($dateString)->setHour(7)->setMinute(rand(16, 59))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                        } else if ($rand <= 30) {
                            $status = 'permission';
                        } else if ($rand <= 40) {
                            $status = 'sick';
                        } else if ($rand <= 50) {
                            // Alpha tidak masuk tabel Attendance
                            continue; 
                        } else {
                            $clockIn = Carbon::parse($dateString)->setHour(7)->setMinute(rand(0, 15))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                        }
                    } else {
                        // Reguler: Mayoritas hadir normal (07:00 - 07:15), kadang terlambat/sakit
                        $rand = rand(1, 100);
                        if ($rand <= 5) {
                            $status = 'late';
                            $clockIn = Carbon::parse($dateString)->setHour(7)->setMinute(rand(16, 30))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                        } else if ($rand <= 10) {
                            $status = 'sick';
                        } else if ($rand <= 15) {
                            $status = 'permission';
                        } else {
                            $clockIn = Carbon::parse($dateString)->setHour(7)->setMinute(rand(0, 15))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                        }
                    }

                    if ($status === 'present' || $status === 'late') {
                        $clockOut = Carbon::parse($dateString)->setHour(15)->setMinute(rand(0, 30))->setSecond(rand(0, 59))->format('Y-m-d H:i:s');
                    }

                    $allAttendances[] = [
                        'user_id' => $user->id,
                        'tenant_id' => $tenantId,
                        'date' => $dateString,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockOut,
                        'status' => $status,
                        'face_match_score' => ($status === 'present' || $status === 'late') ? rand(85, 99) : null,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];

                    // Insert massal setiap 1000 data agar lebih optimal dan cepat di MySQL
                    if (count($allAttendances) >= 1000) {
                        Attendance::insert($allAttendances);
                        $allAttendances = [];
                    }
                }
            }
        }

        // Insert sisa data yang kurang dari 1000
        if (count($allAttendances) > 0) {
            Attendance::insert($allAttendances);
        }

        $this->command->info("Attendance Seeder sukses dijalankan! Data absensi simulasi untuk 4 bulan telah dibuat.");
    }
}
