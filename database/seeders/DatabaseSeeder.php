<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        if (app()->environment('local', 'testing')) {
            $tenantData = [
                'name' => 'SMA Negeri 2 Yogyakarta',
                'subdomain' => 'sman2jogja',
                'slug' => 'sma-negeri-2-yogyakarta',
                'onboarding_completed' => true,
                'institution_type' => 'education',
                'business_category' => 'education',
            ];
            if (Schema::hasColumn('tenants', 'npsn')) {
                $tenantData['npsn'] = '20261001';
            }

            $tenant = Tenant::firstOrCreate(
                ['code' => '20261001'],
                $tenantData
            );

            $this->call([
                HadirSekolahSeeder::class,
                StudentSeeder::class,
                DummyDevSeeder::class,
            ]);

            // Setup Pak Bambang as Homeroom Teacher
            $bambang = User::where('email', 'bambang@example.com')->first();
            if ($bambang) {
                $class = SchoolClass::where('tenant_id', $tenant->id)->first();
                if ($class) {
                    $class->wali_kelas_id = $bambang->id;
                    $class->save();
                }
            }

            // ATURAN MUTLAK: Setiap Kelas Wajib Memiliki Wali Kelas
            $unassignedClasses = SchoolClass::whereNull('wali_kelas_id')->get();
            foreach ($unassignedClasses as $class) {
                $teacherEmail = 'guru.'.Str::slug($class->nama_kelas ?: ('class-'.$class->id)).'@hadirsekolah.id';
                $teacher = User::firstOrCreate(
                    ['email' => $teacherEmail],
                    [
                        'tenant_id' => $class->tenant_id,
                        'name' => 'Wali Kelas '.($class->nama_kelas ?: 'Utama'),
                        'password' => Hash::make('password123'),
                        'role' => 'teacher',
                        'is_active' => true,
                        'email_verified_at' => now(),
                    ]
                );
                $class->wali_kelas_id = $teacher->id;
                $class->save();
            }
        }

        $this->call(DemoAccountsSeeder::class);
    }
}
