<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class HadirSekolahSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantName = 'SMA Negeri 2 Yogyakarta';
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['code' => '20261001'],
            [
                'name' => $tenantName,
                'subdomain' => 'sman2jogja',
                'slug' => Str::slug($tenantName),
                'onboarding_completed' => true,
                'institution_type' => 'education',
                'business_category' => 'education'
            ]
        );

        $defaultPassword = \Illuminate\Support\Facades\Hash::make('password');

        // 1. KEPALA SEKOLAH
        $kepsek = \App\Models\User::updateOrCreate(
            ['email' => 'kepala@sman2yogyakarta.sch.id'],
            [
                'name' => 'Drs. H. Supriyadi, M.Pd.',
                'password' => $defaultPassword,
                'role' => 'headmaster',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        \App\Models\User::updateOrCreate(
            ['email' => 'kepsek.pleret@hadirsekolah.id'],
            [
                'name' => 'Drs. H. Supriyadi, M.Pd.',
                'password' => $defaultPassword,
                'role' => 'headmaster',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. OPERATOR SEKOLAH
        $admin = \App\Models\User::updateOrCreate(
            ['email' => 'admin.dapodik@hadirsekolah.id'],
            [
                'name' => 'Maya Kartika, S.Kom.',
                'password' => $defaultPassword,
                'role' => 'operator',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. WALI KELAS / GURU
        $guruMath = \App\Models\User::updateOrCreate(
            ['email' => 'guru.matematika@hadirsekolah.id'],
            [
                'name' => 'Bambang Hermanto, S.Pd.',
                'password' => $defaultPassword,
                'role' => 'teacher',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $guruIpa = \App\Models\User::updateOrCreate(
            ['email' => 'guru.ipa@hadirsekolah.id'],
            [
                'name' => 'Dr. Ani Wijaya',
                'password' => $defaultPassword,
                'role' => 'teacher',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $guruEnglish = \App\Models\User::updateOrCreate(
            ['email' => 'guru.english@hadirsekolah.id'],
            [
                'name' => 'Budi Santoso, M.Hum.',
                'password' => $defaultPassword,
                'role' => 'teacher',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 4. ORANG TUA / PARENT
        $ortuRizky = \App\Models\User::updateOrCreate(
            ['email' => 'ortu.rizky@gmail.com'],
            [
                'name' => 'Bpk. Pratama',
                'password' => $defaultPassword,
                'role' => 'parent',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $ortuAulia = \App\Models\User::updateOrCreate(
            ['email' => 'ortu.aulia@gmail.com'],
            [
                'name' => 'Ibu Putri',
                'password' => $defaultPassword,
                'role' => 'parent',
                'tenant_id' => $tenant->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 5. SISWA / STUDENT
        $rizky = \App\Models\User::updateOrCreate(
            ['email' => 'rizky.pratama@gmail.com'],
            [
                'name' => 'Rizky Pratama',
                'nisn' => '20260001_R',
                'password' => $defaultPassword,
                'role' => 'student',
                'tenant_id' => $tenant->id,
                'parent_id' => $ortuRizky->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $aulia = \App\Models\User::updateOrCreate(
            ['email' => 'aulia.putri@gmail.com'],
            [
                'name' => 'Aulia Putri',
                'nisn' => '20260002_A',
                'password' => $defaultPassword,
                'role' => 'student',
                'tenant_id' => $tenant->id,
                'parent_id' => $ortuAulia->id,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // JADWAL PELAJARAN KBM MINGGUAN (Kelas IX-A)
        $schedulesData = [
            // Senin
            [
                'name' => 'Matematika - Kelas IX-A',
                'day_of_week' => 1,
                'start_time' => '07:00:00',
                'end_time' => '08:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id,
            ],
            [
                'name' => 'IPA - Kelas IX-A',
                'day_of_week' => 1,
                'start_time' => '08:30:00',
                'end_time' => '10:00:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruIpa->id,
            ],
            // Selasa
            [
                'name' => 'B. Inggris - Kelas IX-A',
                'day_of_week' => 2,
                'start_time' => '07:00:00',
                'end_time' => '08:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruEnglish->id,
            ],
            [
                'name' => 'Matematika - Kelas IX-A',
                'day_of_week' => 2,
                'start_time' => '08:30:00',
                'end_time' => '10:00:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id,
            ],
            // Rabu
            [
                'name' => 'IPA - Kelas IX-A',
                'day_of_week' => 3,
                'start_time' => '07:00:00',
                'end_time' => '08:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruIpa->id,
            ],
            [
                'name' => 'B. Indonesia - Kelas IX-A',
                'day_of_week' => 3,
                'start_time' => '08:30:00',
                'end_time' => '10:00:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id, // Wali kelas
            ],
            // Kamis
            [
                'name' => 'Matematika - Kelas IX-A',
                'day_of_week' => 4,
                'start_time' => '07:00:00',
                'end_time' => '08:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id,
            ],
            [
                'name' => 'B. Inggris - Kelas IX-A',
                'day_of_week' => 4,
                'start_time' => '08:30:00',
                'end_time' => '10:00:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruEnglish->id,
            ],
            // Jumat
            [
                'name' => 'Agama / Budi Pekerti - Kelas IX-A',
                'day_of_week' => 5,
                'start_time' => '07:00:00',
                'end_time' => '08:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id, // Default assign
            ],
            [
                'name' => 'Olahraga / KBM Ringkas - Kelas IX-A',
                'day_of_week' => 5,
                'start_time' => '08:30:00',
                'end_time' => '09:30:00',
                'grace_period_minutes' => 15,
                'manager_id' => $guruMath->id, // Default assign
            ],
        ];

        foreach ($schedulesData as $data) {
            $schedule = \App\Models\Schedule::updateOrCreate(
                [
                    'tenant_id' => $tenant->id,
                    'name' => $data['name'],
                    'type' => 'weekly',
                    'day_of_week' => $data['day_of_week'],
                ],
                [
                    'start_time' => $data['start_time'],
                    'end_time' => $data['end_time'],
                    'grace_period_minutes' => $data['grace_period_minutes'],
                ]
            );
        }
    }
}
