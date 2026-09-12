<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create(['role' => 'admin']);

        $response = $this->post('/login', [
            'school_code' => '20102026',
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('operator.dashboard', absolute: false));
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create();

        $this->post('/login', [
            'school_code' => '20102026',
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_not_authenticate_with_invalid_school_code(): void
    {
        $user = User::factory()->create();

        $response = $this->post('/login', [
            'school_code' => 'WRONGSCHOOL',
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('school_code');
    }

    public function test_teacher_can_authenticate_using_email(): void
    {
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['code' => '20261001'],
            ['name' => 'SMA Negeri 2 Yogyakarta', 'subdomain' => 'sman2jogja', 'slug' => 'sma-negeri-2-yogyakarta', 'onboarding_completed' => true]
        );

        $teacher = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => '198809202014021001@guru.hadirsekolah.id',
            'role' => 'teacher',
        ]);

        $response = $this->post('/login', [
            'school_code' => '20261001',
            'email' => '198809202014021001@guru.hadirsekolah.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($teacher);
        $response->assertRedirect(route('teacher.dashboard', absolute: false));
    }

    public function test_student_can_authenticate_using_email(): void
    {
        $tenant = \App\Models\Tenant::firstOrCreate(
            ['code' => '20261001'],
            ['name' => 'SMA Negeri 2 Yogyakarta', 'subdomain' => 'sman2jogja', 'slug' => 'sma-negeri-2-yogyakarta', 'onboarding_completed' => true]
        );

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'email' => '0012345678@siswa.hadirsekolah.id',
            'role' => 'student',
        ]);

        $response = $this->post('/login', [
            'school_code' => '20261001',
            'email' => '0012345678@siswa.hadirsekolah.id',
            'password' => 'password',
        ]);

        $this->assertAuthenticatedAs($student);
    }

    public function test_student_login_normalizes_email_and_resolves_tenant_by_npsn(): void
    {
        $tenant = Tenant::create([
            'name' => 'Mentari Kita',
            'code' => 'MENTARI',
            'npsn' => '45806592',
            'subdomain' => 'mentari-kita',
            'slug' => 'mentari-kita',
            'onboarding_completed' => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
            'email' => 'indahmayasari883@mentari-kita.hadiryuk.id',
            'password' => '7337679225',
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'school_code' => ' 45806592 ',
            'email' => ' INDAHMAYASARI883@MENTARI-KITA.HADIRYUK.ID ',
            'password' => '7337679225',
        ]);

        $this->assertAuthenticatedAs($student);
        $response->assertRedirect(route('student.dashboard', absolute: false));
    }

    public function test_student_can_authenticate_using_nisn_or_nis(): void
    {
        $tenant = Tenant::create([
            'name' => 'Mentari Kita',
            'code' => 'MENTARI',
            'subdomain' => 'mentari-kita',
            'slug' => 'mentari-kita',
            'onboarding_completed' => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
            'nisn' => '7337679225',
            'nis' => '546166982',
            'password' => Hash::make('password-siswa'),
        ]);

        foreach (['7337679225', '546166982'] as $identifier) {
            $this->post('/login', [
                'school_code' => $tenant->code,
                'email' => $identifier,
                'password' => 'password-siswa',
            ]);

            $this->assertAuthenticatedAs($student);
            $this->post('/logout');
        }
    }

    public function test_student_legacy_default_password_is_reconciled_to_nisn(): void
    {
        $tenant = Tenant::create([
            'name' => 'Mentari Kita',
            'code' => 'MENTARI',
            'subdomain' => 'mentari-kita',
            'slug' => 'mentari-kita',
            'onboarding_completed' => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
            'nisn' => '7337679225',
            'nis' => '546166982',
            'birth_date' => '2009-04-06',
            'password' => Hash::make('legacy-password-format'),
            'is_active' => true,
        ]);

        $this->post('/login', [
            'school_code' => $tenant->code,
            'email' => '7337679225',
            'password' => '733767922506042009',
        ]);

        $this->assertAuthenticatedAs($student);
        $this->assertTrue(Hash::check('7337679225', $student->fresh()->password));
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
