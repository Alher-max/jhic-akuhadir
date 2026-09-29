<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Schedule;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ScheduleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Bersihkan jadwal lama
        DB::table('schedule_user')->delete();
        Schedule::query()->delete();

        // Ambil tenant pertama (asumsi tenant default sudah ada)
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('Tidak ada tenant ditemukan! Harap seed tenant terlebih dahulu.');
            return;
        }

        $tenantId = $tenant->id;

        // Pastikan kelas-kelas SMA ada
        $classNames = ['X IPA 1', 'XI IPA 1', 'XII IPA 1'];
        $classes = [];
        foreach ($classNames as $index => $name) {
            $tingkat = 10 + $index;
            $classes[$name] = SchoolClass::firstOrCreate(
                ['nama_kelas' => $name, 'tenant_id' => $tenantId],
                ['jenjang' => 'SMA', 'tingkat' => $tingkat]
            );
        }

        // Pastikan ada beberapa guru
        $teacherNames = ['Budi Santoso', 'Siti Aminah', 'Ahmad Dahlan', 'Dewi Lestari', 'Agus Prayitno'];
        $teachers = [];
        foreach ($teacherNames as $index => $name) {
            $teachers[] = User::firstOrCreate(
                ['email' => 'guru' . $index . '@example.com'],
                [
                    'name' => $name,
                    'password' => Hash::make('password'),
                    'role' => 'wali_kelas',
                    'tenant_id' => $tenantId,
                    'is_active' => true
                ]
            );
        }

        $subjects = [
            'Matematika', 'IPA Terpadu', 'Bahasa Indonesia', 'Bahasa Inggris', 
            'IPS Terpadu', 'Informatika', 'Pendidikan Agama', 'PJOK'
        ];

        $slots = [
            ['start' => '07:00', 'end' => '08:30'],
            ['start' => '08:30', 'end' => '10:00'],
            ['start' => '10:15', 'end' => '11:45'],
        ];

        // Buat jadwal untuk setiap hari (Senin=1 s.d Jumat=5)
        for ($day = 1; $day <= 5; $day++) {
            foreach ($classNames as $className) {
                $classModel = $classes[$className];
                
                // Tiap kelas punya jadwal penuh (3 slot/hari)
                foreach ($slots as $index => $slot) {
                    $randomSubject = $subjects[array_rand($subjects)];
                    $randomTeacher = $teachers[array_rand($teachers)];
                    
                        Schedule::create([
                        'tenant_id' => $tenantId,
                        'name' => $randomSubject,
                        'type' => 'routine',
                        'day_of_week' => $day,
                        'start_time' => $slot['start'],
                        'end_time' => $slot['end'],
                        'grace_period_minutes' => 15,
                        'class_id' => $classModel->id,
                        'teacher_id' => $randomTeacher->id,
                    ]);
                }
            }
        }
        
        $this->command->info('Mockup Jadwal Mingguan berhasil diisi!');
    }
}
