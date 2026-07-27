<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Test',
            'institution_type' => 'school',
            'slug' => 'sman-1-test',
            'code' => 'SMA1TEST',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => '10-IPA-1',
        ]);
    }

    /**
     * test: allows activation for registered student using NISN or email without school code
     */
    public function test_allows_activation_for_registered_student_using_nisn_or_email_without_school_code(): void
    {
        // 1. Buat record siswa belum aktif di sistem (dibuat oleh operator/sekolah)
        $student = User::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'name' => 'Ahmad Rizky Pratama',
            'nisn' => '1029384756',
            'email' => 'ahmadrizkypratama269@student.com',
            'password' => Hash::make('unactivated'),
            'role' => 'student',
            'is_active' => false,
            'email_verified_at' => null,
            'onboarding_completed' => true,
        ]);

        // 2. Kirim request aktivasi menggunakan NISN (atau Email) tanpa Kode Sekolah
        $response = $this->post(route('student.activate.store'), [
            'nisn_or_email' => '1029384756',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        // 3. Assert redirect ke dashboard & siswa ter-autentikasi (login)
        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($student);

        // 4. Assert password ter-update & status akun aktif
        $student->refresh();
        $this->assertTrue((bool) $student->is_active);
        $this->assertTrue(Hash::check('NewPassword123!', $student->password));
        $this->assertNotNull($student->email_verified_at);
    }

    /**
     * test: rejects activation if NISN or email is not found in database
     */
    public function test_rejects_activation_if_nisn_or_email_is_not_found_in_database(): void
    {
        // Kirim NISN/Email yang tidak terdaftar
        $response = $this->post(route('student.activate.store'), [
            'nisn_or_email' => '9999999999',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors(['nisn_or_email']);
    }

    /**
     * test: rejects activation if student account is already active
     */
    public function test_rejects_activation_if_student_account_is_already_active(): void
    {
        // 1. Buat record siswa yang sudah aktif
        User::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'name' => 'Siswa Aktif',
            'nisn' => '1029384756',
            'email' => 'siswaaktif@student.com',
            'password' => Hash::make('existing-password'),
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // 2. Coba aktivasi ulang
        $response = $this->post(route('student.activate.store'), [
            'nisn_or_email' => '1029384756',
            'password' => 'NewPassword123!',
            'password_confirmation' => 'NewPassword123!',
        ]);

        $response->assertSessionHasErrors(['nisn_or_email']);
    }

    /**
     * test: allows creating a new student without parent account (optional parent relation)
     */
    public function test_allows_creating_new_student_without_parent_account(): void
    {
        $admin = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Operator Sekolah',
            'email' => 'operator@sman1test.sch.id',
            'email_verified_at' => now(),
            'password' => Hash::make('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)
            ->post(route('students.store'), [
                'name' => 'Budi Tanpa Ortu',
                'nisn' => '0081234567',
                'nis' => '202410012',
                'class_id' => $this->schoolClass->id,
                'birth_date' => '2008-05-15',
                'parent_option' => 'none',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $student = \App\Models\Student::where('nisn', '0081234567')->first();
        $this->assertNotNull($student);
        $this->assertEquals('Budi Tanpa Ortu', $student->name);
        $this->assertNull($student->parent_id);
    }
}
