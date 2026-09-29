<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\DemoAccountsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    private const ACCOUNTS = [
        'operator.demo@hadiryuk.id' => 'operator',
        'kepala.demo@hadiryuk.id' => 'headmaster',
        'guru.demo@hadiryuk.id' => 'teacher',
        'walikelas.demo@hadiryuk.id' => 'wali_kelas',
        'orangtua.demo@hadiryuk.id' => 'parent',
        'siswa.demo@hadiryuk.id' => 'student',
    ];

    public function test_demo_accounts_are_repeatable_and_have_connected_school_data(): void
    {
        $this->seed(DemoAccountsSeeder::class);
        $this->seed(DemoAccountsSeeder::class);

        $users = User::whereIn('email', array_keys(self::ACCOUNTS))->get()->keyBy('email');
        $this->assertCount(6, $users);

        foreach (self::ACCOUNTS as $email => $role) {
            $this->assertSame($role, $users[$email]->role);
            $this->assertTrue(Hash::check('password123', $users[$email]->password));
        }

        $student = $users['siswa.demo@hadiryuk.id'];
        $parent = $users['orangtua.demo@hadiryuk.id'];
        $homeroomTeacher = $users['walikelas.demo@hadiryuk.id'];

        $this->assertSame('XII IPA Demo', $student->schoolClass->nama_kelas);
        $this->assertSame($homeroomTeacher->id, $student->schoolClass->wali_kelas_id);
        $this->assertTrue($parent->students()->whereKey($student->id)->exists());
        $this->assertSame(30, Attendance::whereIn('user_id', $users->pluck('id'))->count());
    }

    public function test_demo_homepage_and_login_prefill_are_available(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Coba Akun Demo')
            ->assertSee('operator.demo@hadiryuk.id')
            ->assertSee('password123');

        $this->get('/login?school_code=JHIC2026&email=operator.demo%40hadiryuk.id')
            ->assertOk()
            ->assertSee('value="JHIC2026"', false)
            ->assertSee('value="operator.demo@hadiryuk.id"', false);
    }

    public function test_all_demo_accounts_can_log_in_with_the_published_credentials(): void
    {
        $this->seed(DemoAccountsSeeder::class);

        foreach (self::ACCOUNTS as $email => $role) {
            $user = User::where('email', $email)->firstOrFail();

            $this->post('/login', [
                'school_code' => 'JHIC2026',
                'email' => $email,
                'password' => 'password123',
            ])->assertSessionHasNoErrors();

            $this->assertAuthenticatedAs($user);
            $this->post('/logout');
        }
    }
}
