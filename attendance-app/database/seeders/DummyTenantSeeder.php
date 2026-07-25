<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DummyTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $tenantCode = '2DJBUV';
        $subdomain = strtolower($tenantCode);
        
        // 1. INSTITUSI / TENANT
        $tenant = Tenant::updateOrCreate(
            ['code' => $tenantCode],
            [
                'name' => 'SMPN 1 Pleret',
                'slug' => Str::slug('SMPN 1 Pleret') . '-' . strtolower($tenantCode),
                'institution_type' => 'Sekolah / Madrasah',
                'subdomain' => $subdomain,
            ]
        );

        $now = Carbon::now();
        $password = Hash::make('password123');

        // 2. USER 1 - PIMPINAN (OWNER)
        User::updateOrCreate(
            ['email' => 'pimpinan.pleret@hadiryuk.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dr. Hendra Wijaya',
                'password' => $password,
                'role' => 'owner',
                'onboarding_completed' => true,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 3. USER 2 - GURU (MANAGER)
        User::updateOrCreate(
            ['email' => 'guru.pleret@hadiryuk.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Dewani Rahma, S.Pd.',
                'password' => $password,
                'role' => 'manager_teacher',
                'onboarding_completed' => true,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 4. USER 3 - SISWA SMP/SMA (DENGAN EMAIL)
        User::updateOrCreate(
            ['email' => 'rizky.pratama@gmail.com'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Rizky Pratama',
                'nisn' => '20260001',
                'password' => $password,
                'role' => 'staff_student',
                'onboarding_completed' => true,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );

        // 5. USER 4 - SISWA SD (TANPA EMAIL / VIRTUAL EMAIL)
        User::updateOrCreate(
            ['email' => '20260002@2djbuv.hadiryuk.id'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Aulia Putri',
                'nisn' => '20260002',
                'password' => $password,
                'role' => 'staff_student',
                'onboarding_completed' => true,
                'is_active' => true,
                'email_verified_at' => $now,
            ]
        );
    }
}
