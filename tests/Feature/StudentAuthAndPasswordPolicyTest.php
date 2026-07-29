<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use Tests\TestCase;

class StudentAuthAndPasswordPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected User $teacher;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'code' => '20265001',
            'slug' => 'sman1jkt',
            'onboarding_completed' => true,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'name' => 'Operator Sekolah',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $this->teacher = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'teacher',
            'name' => 'Budi Hermanto, S.Pd.',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $this->schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 1',
            'wali_kelas_id' => $this->teacher->id,
        ]);
    }

    /**
     * Test 1: Siswa yang dibuat oleh Operator otomatis memiliki email_verified_at tidak null.
     */
    public function test_student_created_by_operator_has_auto_verified_email(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('students.store'), [
                'name' => 'Siswa Baru Otomatis',
                'nisn' => '1098765432',
                'nis' => '2026001',
                'gender' => 'L',
                'birth_date' => '2010-05-15',
                'class_id' => $this->schoolClass->id,
            ]);

        $response->assertRedirect();
        
        $student = User::where('tenant_id', $this->tenant->id)->where('nisn', '1098765432')->first();
        $this->assertNotNull($student);
        $this->assertNotNull($student->email_verified_at);
    }

    /**
     * Test 2: Login akun siswa langsung berhasil tanpa redirect verifikasi OTP.
     */
    public function test_student_login_bypasses_otp_redirect(): void
    {
        $student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'email' => 'siswa.test@hadiryuk.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => null, // Belum terverifikasi awal
        ]);

        $response = $this->post(route('login'), [
            'email' => 'siswa.test@hadiryuk.id',
            'password' => 'password123',
            'school_code' => $this->tenant->code,
        ]);

        $response->assertRedirect(route('student.dashboard', absolute: false));
        $this->assertAuthenticatedAs($student);
        $student->refresh();
        $this->assertNotNull($student->email_verified_at);
    }

    /**
     * Test 3: Login akun guru/operator tetap membutuhkan verifikasi OTP jika email belum terverifikasi.
     */
    public function test_teacher_and_operator_login_requires_otp_verification(): void
    {
        $unverifiedTeacher = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'teacher',
            'email' => 'guru.unverified@hadiryuk.id',
            'password' => Hash::make('password123'),
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'guru.unverified@hadiryuk.id',
            'password' => 'password123',
            'school_code' => $this->tenant->code,
        ]);

        $response->assertRedirect(route('register.verify-otp'));
        $this->assertGuest();
    }

    /**
     * Test 4: Operator dapat mereset password siswa ke default.
     */
    public function test_operator_can_reset_student_password_to_default(): void
    {
        $student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'name' => 'Siswa Reset',
            'nisn' => '9988776655',
            'password' => Hash::make('passwordLama123'),
            'class_id' => $this->schoolClass->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('students.reset-password', $student->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertTrue(Hash::check('9988776655', $student->password));
        $this->assertFalse($student->is_password_changed);
    }

    /**
     * Test 5: Wali Kelas dapat mereset password siswa di kelas binaannya.
     */
    public function test_homeroom_teacher_can_reset_student_password_in_their_class(): void
    {
        $student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'name' => 'Siswa Asuhan',
            'nisn' => '1122334455',
            'password' => Hash::make('passwordRandom'),
            'class_id' => $this->schoolClass->id,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->teacher)
            ->post(route('students.reset-password', $student->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $student->refresh();
        $this->assertTrue(Hash::check('1122334455', $student->password));
    }
}
