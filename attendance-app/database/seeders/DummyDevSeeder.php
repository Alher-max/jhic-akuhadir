<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DummyDevSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenant = \App\Models\Tenant::first() ?? \App\Models\Tenant::create([
            'code' => '2DJBUV',
            'name' => 'SMPN 1 Pleret',
            'subdomain' => 'smpn1pleret',
            'slug' => 'smpn1pleret',
            'onboarding_completed' => true,
            'institution_type' => 'education',
            'business_category' => 'education'
        ]);
        $tenantId = $tenant->id;
        $today = Carbon::today();

        // 1. Create Wali Kelas
        $waliKelas = User::firstOrCreate(
            ['email' => 'bambang@example.com'],
            [
                'tenant_id' => $tenantId,
                'name' => 'Bambang Hermanto, S.Pd.',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'is_active' => true,
            ]
        );

        // 2. Create 3 Classes
        $classesData = [
            ['jenjang' => 'SMA', 'tingkat' => 10, 'nama_kelas' => 'X IPA 1'],
            ['jenjang' => 'SMA', 'tingkat' => 11, 'nama_kelas' => 'XI IPA 1'],
            ['jenjang' => 'SMA', 'tingkat' => 12, 'nama_kelas' => 'XII IPA 1'],
        ];

        $classes = [];
        foreach ($classesData as $data) {
            $class = SchoolClass::firstOrCreate(
                ['tenant_id' => $tenantId, 'nama_kelas' => $data['nama_kelas']],
                ['jenjang' => $data['jenjang'], 'tingkat' => $data['tingkat'], 'wali_kelas_id' => $waliKelas->id]
            );
            // Ensure wali_kelas_id is set
            $class->wali_kelas_id = $waliKelas->id;
            $class->save();
            $classes[] = $class;
        }

        // 3. Create Students and Attendances
        // X IPA 1 (32 students)
        // XI IPA 1 (34 students)
        // XII IPA 1 (30 students)
        // Total 96 students.

        $studentsConfig = [
            'X IPA 1' => 32,
            'XI IPA 1' => 34,
            'XII IPA 1' => 30,
        ];

        // Delete existing mock students for this script run
        $existingStudents = User::where('tenant_id', $tenantId)->where('role', 'student')->where('email', 'like', 'mock_student_%@example.com')->get();
        foreach ($existingStudents as $s) {
            Attendance::where('user_id', $s->id)->delete();
            $s->delete();
        }

        $allNewStudents = [];

        foreach ($classes as $class) {
            $numStudents = $studentsConfig[$class->nama_kelas];
            for ($i = 1; $i <= $numStudents; $i++) {
                $student = User::create([
                    'tenant_id' => $tenantId,
                    'name' => "Siswa {$class->nama_kelas} #{$i}",
                    'email' => "mock_student_" . Str::slug($class->nama_kelas) . "_{$i}@example.com",
                    'password' => Hash::make('password123'),
                    'role' => 'student',
                    'is_active' => true,
                    'class_id' => $class->id
                ]);
                $allNewStudents[] = $student;
            }
        }

        // Target attendance: 82 present, 5 leave/sick, 9 absent (belum absen)
        // Total 96 students.
        $presentStudents = array_slice($allNewStudents, 0, 82);
        $leaveStudents = array_slice($allNewStudents, 82, 5);

        foreach ($presentStudents as $student) {
            Attendance::create([
                'tenant_id' => $tenantId,
                'user_id' => $student->id,
                'date' => $today->format('Y-m-d'),
                'clock_in' => $today->copy()->setHour(6)->setMinute(rand(30, 59))->format('Y-m-d H:i:s'),
                'status' => 'present',
            ]);
        }

        foreach ($leaveStudents as $student) {
            \App\Models\LeaveRequest::create([
                'tenant_id' => $tenantId,
                'user_id' => $student->id,
                'type' => 'sick',
                'start_date' => $today->format('Y-m-d'),
                'end_date' => $today->format('Y-m-d'),
                'reason' => 'Sakit demam',
                'status' => 'approved',
            ]);
        }
    }
}
