<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassManagementValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected User $teacher1;
    protected User $teacher2;
    protected SchoolClass $class1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'code' => '20102026',
            'slug' => 'sman1jkt',
            'onboarding_completed' => true,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->teacher1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'teacher',
            'name' => 'Budi Santoso, S.Pd.',
            'is_active' => true,
        ]);

        $this->teacher2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'teacher',
            'name' => 'Dr. Ani Wijaya',
            'is_active' => true,
        ]);

        $this->class1 = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 1',
            'wali_kelas_id' => $this->teacher1->id,
        ]);
    }

    /**
     * Test: Gagal membuat kelas baru jika guru yang dipilih sudah menjadi wali kelas di rombel lain.
     */
    public function test_cannot_assign_same_teacher_as_homeroom_to_multiple_classes_on_store(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('operator.classes.store'), [
                'jenjang' => 'SMA',
                'tingkat' => 10,
                'nama_kelas' => 'X IPA 2',
                'wali_kelas_id' => $this->teacher1->id, // Sudah wali kelas X IPA 1
            ]);

        $response->assertSessionHasErrors(['wali_kelas_id']);
        $errors = session('errors')->get('wali_kelas_id');
        $this->assertContains('Guru ini sudah ditugaskan menjadi Wali Kelas di rombel lain.', $errors);
    }

    /**
     * Test: Berhasil membuat kelas baru dengan guru yang belum menjadi wali kelas di mana pun.
     */
    public function test_can_assign_free_teacher_as_homeroom_on_store(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('operator.classes.store'), [
                'jenjang' => 'SMA',
                'tingkat' => 10,
                'nama_kelas' => 'X IPA 2',
                'wali_kelas_id' => $this->teacher2->id,
            ]);

        $response->assertRedirect(route('operator.classes.index'));
        $this->assertDatabaseHas('school_classes', [
            'nama_kelas' => 'X IPA 2',
            'wali_kelas_id' => $this->teacher2->id,
        ]);
    }

    /**
     * Test: Gagal mengubah wali kelas suatu rombel ke guru yang sudah menjadi wali kelas di rombel lain.
     */
    public function test_cannot_update_class_to_teacher_who_is_already_homeroom_in_another_class(): void
    {
        $class2 = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 2',
            'wali_kelas_id' => $this->teacher2->id,
        ]);

        // Coba update class2 dengan wali_kelas_id teacher1 (yang sudah wali kelas class1)
        $response = $this->actingAs($this->operator)
            ->put(route('operator.classes.update', $class2->id), [
                'jenjang' => 'SMA',
                'tingkat' => 10,
                'nama_kelas' => 'X IPA 2',
                'wali_kelas_id' => $this->teacher1->id,
            ]);

        $response->assertSessionHasErrors(['wali_kelas_id']);
        $errors = session('errors')->get('wali_kelas_id');
        $this->assertContains('Guru ini sudah ditugaskan menjadi Wali Kelas di rombel lain.', $errors);
    }

    /**
     * Test: Berhasil meng-update kelas dengan mempertahankan wali kelas yang sama.
     */
    public function test_can_update_class_keeping_same_homeroom_teacher(): void
    {
        $response = $this->actingAs($this->operator)
            ->put(route('operator.classes.update', $this->class1->id), [
                'jenjang' => 'SMA',
                'tingkat' => 10,
                'nama_kelas' => 'X IPA 1 (Revisi)',
                'wali_kelas_id' => $this->teacher1->id,
            ]);

        $response->assertRedirect(route('operator.classes.index'));
        $this->assertDatabaseHas('school_classes', [
            'id' => $this->class1->id,
            'nama_kelas' => 'X IPA 1 (Revisi)',
            'wali_kelas_id' => $this->teacher1->id,
        ]);
    }
}
