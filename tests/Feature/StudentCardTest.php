<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Student;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentCardTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected Student $student;
    protected SchoolClass $schoolClass;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SD Negeri 1 Test',
            'institution_type' => 'school',
            'slug' => 'sdn-1-test',
            'code' => 'SD1TEST',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
            'email_verified_at' => now(),
            'onboarding_completed' => true,
        ]);

        $this->schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SD',
            'tingkat' => 1,
            'nama_kelas' => '1-A',
            'wali_kelas_id' => $this->operator->id,
        ]);

        $this->student = Student::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'name' => 'Budi Santoso',
            'email' => 'budi.santoso@test.id',
            'password' => bcrypt('password123'),
            'nisn' => '0098765432',
            'nis' => '2026001',
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * Test: Operator dapat mengakses halaman pengelolaan Kartu Pelajar.
     */
    public function test_user_can_access_student_cards_index_page(): void
    {
        $response = $this->actingAs($this->operator)
            ->get(route('student-cards.index'));

        $response->assertStatus(200);
        $response->assertSee('Template Kartu Pelajar');
        $response->assertSee('Budi Santoso');
    }

    /**
     * Test: Operator dapat meng-generate halaman pratinjau cetak kartu pelajar dengan QR Code unik.
     */
    public function test_user_can_generate_student_card_print_preview_with_qr_code(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('student-cards.print'), [
                'student_ids' => [$this->student->id],
                'template' => 'modern',
                'accent_color' => 'indigo',
                'school_name' => 'SD NEGERI 1 TEST',
                'academic_year' => '2026/2027',
                'card_title' => 'KARTU PELAJAR',
                'footer_text' => 'Kartu ini wajib dibawa saat presensi.',
            ]);

        $response->assertStatus(200);
        $response->assertSee('SD NEGERI 1 TEST');
        $response->assertSee('Budi Santoso');
        $response->assertSee('0098765432'); // Token QR Code (NISN)
    }
}
