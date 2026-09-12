<?php

namespace Database\Seeders;

use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MAN3BantulDemoSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['id' => 10],
            [
                'name' => 'MAN 3 Bantul',
                'code' => 'MAN3BANTUL',
                'npsn' => '53284730',
                'slug' => 'man-3-bantul',
                'subdomain' => 'man3bantul',
                'institution_type' => 'Sekolah / Madrasah',
                'business_category' => 'education',
                'onboarding_completed' => true,
            ]
        );

        $password = Hash::make('password123');
        $classes = collect([
            ['nama_kelas' => 'X IPA 1', 'jenjang' => 'MA', 'tingkat' => 10],
            ['nama_kelas' => 'XI IPA 1', 'jenjang' => 'MA', 'tingkat' => 11],
            ['nama_kelas' => 'XII IPA 1', 'jenjang' => 'MA', 'tingkat' => 12],
        ])->mapWithKeys(function (array $data) use ($tenant): array {
            $class = SchoolClass::updateOrCreate(
                ['tenant_id' => $tenant->id, 'nama_kelas' => $data['nama_kelas']],
                [
                    'jenjang' => $data['jenjang'],
                    'tingkat' => $data['tingkat'],
                    'wali_kelas_id' => null,
                ]
            );

            return [$data['nama_kelas'] => $class];
        });

        $families = [
            [
                'parent_email' => 'dedi.ramadhan@gmail.com',
                'parent_name' => 'Dedi Ramadhan',
                'student_name' => 'Rizky Ramadhan',
                'student_email' => '0087654321@man3bantul.hadiryuk.id',
                'nisn' => '0087654321',
                'class' => 'X IPA 1',
                'relationship' => 'Ayah',
            ],
            [
                'parent_email' => 'siti.aminah@gmail.com',
                'parent_name' => 'Siti Aminah',
                'student_name' => 'Nabila Putri Syahrani',
                'student_email' => '0087654322@man3bantul.hadiryuk.id',
                'nisn' => '0087654322',
                'class' => 'XI IPA 1',
                'relationship' => 'Ibu',
            ],
            [
                'parent_email' => 'hendra.gunawan@gmail.com',
                'parent_name' => 'Hendra Gunawan',
                'student_name' => 'Farhan Maulana Akbar',
                'student_email' => '0087654323@man3bantul.hadiryuk.id',
                'nisn' => '0087654323',
                'class' => 'XII IPA 1',
                'relationship' => 'Ayah',
            ],
        ];

        foreach ($families as $family) {
            $parent = User::updateOrCreate(
                ['email' => $family['parent_email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $family['parent_name'],
                    'password' => $password,
                    'role' => 'parent',
                    'is_active' => true,
                    'onboarding_completed' => true,
                    'email_verified_at' => now(),
                ]
            );

            $student = User::updateOrCreate(
                ['tenant_id' => $tenant->id, 'nisn' => $family['nisn']],
                [
                    'name' => $family['student_name'],
                    'email' => $family['student_email'],
                    'password' => $password,
                    'role' => 'student',
                    'class_id' => $classes[$family['class']]->id,
                    'parent_id' => $parent->id,
                    'is_active' => true,
                    'onboarding_completed' => true,
                    'email_verified_at' => now(),
                ]
            );

            DB::table('parent_student')->updateOrInsert(
                ['parent_id' => $parent->id, 'student_id' => $student->id],
                [
                    'relationship' => $family['relationship'],
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
