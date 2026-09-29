<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoAccountsSeeder extends Seeder
{
    private const SCHOOL_CODE = 'JHIC2026';

    private const PASSWORD = 'password123';

    public function run(): void
    {
        $tenant = Tenant::updateOrCreate(
            ['code' => self::SCHOOL_CODE],
            [
                'name' => 'SMA JHIC 2.0',
                'slug' => 'sma-jhic-2-0-demo',
                'subdomain' => 'jhic2026',
                'institution_type' => 'education',
                'business_category' => 'education',
                'onboarding_completed' => true,
            ]
        );

        $accounts = [
            'operator' => [
                'name' => 'Operator Demo',
                'email' => 'operator.demo@hadiryuk.id',
                'role' => 'operator',
                'position' => 'operator',
            ],
            'headmaster' => [
                'name' => 'Kepala Sekolah Demo',
                'email' => 'kepala.demo@hadiryuk.id',
                'role' => 'headmaster',
                'position' => 'headmaster',
            ],
            'teacher' => [
                'name' => 'Guru Demo',
                'email' => 'guru.demo@hadiryuk.id',
                'role' => 'teacher',
                'position' => 'teacher',
            ],
            'homeroom_teacher' => [
                'name' => 'Wali Kelas Demo',
                'email' => 'walikelas.demo@hadiryuk.id',
                'role' => 'wali_kelas',
                'position' => 'wali_kelas',
            ],
            'parent' => [
                'name' => 'Orang Tua Demo',
                'email' => 'orangtua.demo@hadiryuk.id',
                'role' => 'parent',
                'position' => null,
            ],
            'student' => [
                'name' => 'Siswa Demo',
                'email' => 'siswa.demo@hadiryuk.id',
                'role' => 'student',
                'position' => null,
            ],
        ];

        $users = [];
        foreach ($accounts as $key => $account) {
            $users[$key] = User::updateOrCreate(
                ['email' => $account['email']],
                [
                    'tenant_id' => $tenant->id,
                    'name' => $account['name'],
                    'password' => Hash::make(self::PASSWORD),
                    'role' => $account['role'],
                    'position' => $account['position'],
                    'is_active' => true,
                    'onboarding_completed' => true,
                    'email_verified_at' => now(),
                ]
            );
        }

        $class = SchoolClass::updateOrCreate(
            ['tenant_id' => $tenant->id, 'nama_kelas' => 'XII IPA Demo'],
            [
                'jenjang' => 'SMA',
                'tingkat' => 12,
                'wali_kelas_id' => $users['homeroom_teacher']->id,
            ]
        );

        $users['homeroom_teacher']->homeroomClasses()->save($class);
        $users['student']->class_id = $class->id;
        $users['student']->parent_id = $users['parent']->id;
        $users['student']->nisn = '20260001';
        $users['student']->save();
        $users['parent']->students()->syncWithoutDetaching([
            $users['student']->id => ['relationship' => 'Orang Tua'],
        ]);

        $attendanceStatuses = ['present', 'late', 'present', 'sick', 'present'];
        $attendanceUsers = array_values($users);
        $dates = [];

        for ($offset = 0; count($dates) < count($attendanceStatuses); $offset++) {
            $date = today()->subDays($offset);
            if ($date->isWeekday()) {
                $dates[] = $date;
            }
        }

        foreach ($attendanceUsers as $user) {
            foreach ($dates as $dateIndex => $date) {
                $status = $attendanceStatuses[$dateIndex];
                $clockIn = in_array($status, ['present', 'late'], true)
                    ? $date->copy()->setTime($status === 'late' ? 7 : 6, $status === 'late' ? 18 : 50)
                    : null;

                Attendance::updateOrCreate(
                    ['user_id' => $user->id, 'date' => $date->toDateString()],
                    [
                        'tenant_id' => $tenant->id,
                        'clock_in' => $clockIn,
                        'clock_out' => $clockIn ? $date->copy()->setTime(15, 0) : null,
                        'status' => $status,
                        'recorded_by_user_id' => $users['operator']->id,
                    ]
                );
            }
        }

        $this->command?->info('Enam akun demo JHIC berhasil disiapkan. Kode sekolah: '.self::SCHOOL_CODE);
    }
}
