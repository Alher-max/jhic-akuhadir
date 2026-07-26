<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Faker\Factory as Faker;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            $this->command->error('Tidak ada tenant ditemukan! Harap seed tenant terlebih dahulu.');
            return;
        }

        $tenantId = $tenant->id;
        $faker = Faker::create('id_ID');

        // Pastikan ada beberapa kelas untuk tempat siswa
        $classNames = ['Kelas 10 C Proton', 'IX-A', 'IX-B', 'X-C'];
        $classIds = [];
        foreach ($classNames as $name) {
            $class = SchoolClass::firstOrCreate(
                ['nama_kelas' => $name, 'tenant_id' => $tenantId],
                ['jenjang' => 'SMP/SMA', 'tingkat' => 10]
            );
            $classIds[] = $class->id;
        }

        $this->command->info('Membuat data siswa dummy (impor CSV belum aktif)...');

        $studentsData = [];

        // Siswa spesifik Bunga Lestari (Belum Aktif)
        $studentsData[] = [
            'name' => 'Bunga Lestari',
            'email' => 'bunga.lestari.1029384757@student.hadiryuk.id',
            'nisn' => '1029384757',
            'password' => Hash::make('unactivated'),
            'role' => 'student',
            'class_id' => $classIds[0],
            'is_active' => false,
            'onboarding_completed' => true,
            'email_verified_at' => null,
        ];

        // Siswa spesifik Ahmad Rizky (Belum Aktif)
        $studentsData[] = [
            'name' => 'Ahmad Rizky Pratama',
            'email' => 'ahmadrizky.1029384756@student.hadiryuk.id',
            'nisn' => '1029384756',
            'password' => Hash::make('unactivated'),
            'role' => 'student',
            'class_id' => $classIds[0],
            'is_active' => false,
            'onboarding_completed' => true,
            'email_verified_at' => null,
        ];

        $nisnStart = 2026001001;

        for ($i = 0; $i < 200; $i++) {
            // Hanya 1 siswa contoh yang sudah aktif untuk testing login
            $isActive = ($i === 0);
            $nisn = (string) ($nisnStart + $i);
            $studentName = $faker->name;
            $email = strtolower(\Illuminate\Support\Str::slug($studentName, '.')) . '.' . $nisn . '@student.hadiryuk.id';
            
            $studentsData[] = [
                'name' => $studentName,
                'email' => $email,
                'nisn' => $nisn,
                'password' => $isActive ? Hash::make('password123') : Hash::make('unactivated'),
                'role' => 'student',
                'class_id' => $classIds[array_rand($classIds)],
                'is_active' => $isActive,
                'onboarding_completed' => true,
                'email_verified_at' => $isActive ? Carbon::now() : null,
            ];
        }

        foreach ($studentsData as $data) {
            User::firstOrCreate(
                [
                    'tenant_id' => $tenantId,
                    'nisn' => $data['nisn'],
                ],
                [
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'role' => $data['role'],
                    'class_id' => $data['class_id'],
                    'is_active' => $data['is_active'],
                    'onboarding_completed' => $data['onboarding_completed'],
                    'email_verified_at' => $data['email_verified_at'],
                ]
            );
        }

        $this->command->info('Berhasil membuat data siswa dummy belum aktif!');
    }
}
