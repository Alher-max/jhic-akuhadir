<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

use Tests\TestCase;

class OperatorParentManagementTest extends TestCase
{
    use RefreshDatabase;

    private function createTenant(): Tenant
    {
        return Tenant::create([
            'name' => 'SMK Negeri 1 Test',
            'code' => 'SCH999',
            'slug' => 'smkn1test',
        ]);
    }

    public function test_operator_can_view_parents_list(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'parent',
            'name' => 'Bapak Subagyo',
        ]);

        $response = $this->actingAs($operator)->get(route('operator.parents.index'));

        $response->assertStatus(200);
        $response->assertSee('Bapak Subagyo');
        $response->assertSee('Daftar Orang Tua');
    }

    public function test_operator_can_link_student_to_parent(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'parent',
            'name' => 'Ibu Rahmawati',
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
            'name' => 'Ananda Rizky',
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.link-student', $parent->id), [
            'student_id' => $student->id,
            'relationship' => 'Ibu',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_student', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
            'relationship' => 'Ibu',
        ]);
    }

    public function test_operator_can_unlink_student_from_parent(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'parent',
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
        ]);

        $parent->students()->attach($student->id, ['relationship' => 'Ayah']);

        $response = $this->actingAs($operator)->delete(route('operator.parents.unlink-student', [$parent->id, $student->id]));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('parent_student', [
            'parent_id' => $parent->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_operator_can_reset_parent_password(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'onboarding_completed' => true,
            'is_active' => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'parent',
            'parent_phone' => '081234567890',
            'password' => Hash::make('oldpassword'),
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.reset-password', $parent->id));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $parent = $parent->fresh();
        // $this->assertTrue((bool) $parent->must_change_password);
        // $this->assertFalse((bool) $parent->is_password_changed);
        $this->assertTrue(Hash::check('081234567890', $parent->fresh()->password));
    }
    public function test_operator_can_create_new_parent_account(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.store'), [
            'name'         => 'Bapak Rudi Hartono',
            'email'        => 'rudi.hartono@example.com',
            'parent_phone' => '085612345678',
        ]);

        $response->assertRedirect(route('operator.parents.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'name'      => 'Bapak Rudi Hartono',
            'email'     => 'rudi.hartono@example.com',
            'role'      => 'parent',
            'tenant_id' => $tenant->id,
        ]);

        // Default password harus menggunakan nomor HP
        $parent = User::where('email', 'rudi.hartono@example.com')->first();
        $this->assertNotNull($parent);
        $this->assertTrue(Hash::check('085612345678', $parent->password));
        $this->assertNotNull($parent->email_verified_at);
    }

    public function test_operator_can_create_parent_without_email_auto_generates_dummy_email(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.store'), [
            'name' => 'Ibu Wulandari',
            // email dikosongkan → harus auto-generated
        ]);

        $response->assertRedirect(route('operator.parents.index'));
        $response->assertSessionHas('success');

        $parent = User::where('name', 'Ibu Wulandari')->where('role', 'parent')->first();
        $this->assertNotNull($parent);
        // Email harus auto-generated (mengandung '@hadirsekolah.id')
        $this->assertStringContainsString('@hadirsekolah.id', $parent->email);
        $this->assertEquals($tenant->id, $parent->tenant_id);
    }

    public function test_linking_duplicate_relationship_to_same_student_is_rejected(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'student',
            'name'      => 'Ananda Pratama',
        ]);

        // Ayah pertama sudah terhubung
        $parentAyah1 = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'parent',
            'name'      => 'Bapak Pertama',
        ]);
        $parentAyah1->students()->attach($student->id, ['relationship' => 'Ayah']);

        // Operator mencoba menautkan Ayah kedua ke siswa yang sama → harus ditolak
        $parentAyah2 = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'parent',
            'name'      => 'Bapak Kedua',
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.link-student', $parentAyah2->id), [
            'student_id'   => $student->id,
            'relationship' => 'Ayah',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');

        // Pivot table tidak boleh bertambah entry untuk parent kedua
        $this->assertDatabaseMissing('parent_student', [
            'parent_id'    => $parentAyah2->id,
            'student_id'   => $student->id,
            'relationship' => 'Ayah',
        ]);
    }

    public function test_linking_different_relationship_to_student_with_existing_link_succeeds(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'student',
            'name'      => 'Bunga Melati',
        ]);

        // Ayah sudah terhubung
        $parentAyah = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'parent',
            'name'      => 'Bapak Suharjo',
        ]);
        $parentAyah->students()->attach($student->id, ['relationship' => 'Ayah']);

        // Operator menautkan Ibu (hubungan berbeda) → harus berhasil
        $parentIbu = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'parent',
            'name'      => 'Ibu Rahayu',
        ]);

        $response = $this->actingAs($operator)->post(route('operator.parents.link-student', $parentIbu->id), [
            'student_id'   => $student->id,
            'relationship' => 'Ibu',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('parent_student', [
            'parent_id'    => $parentIbu->id,
            'student_id'   => $student->id,
            'relationship' => 'Ibu',
        ]);

        // Ayah lama tetap ada
        $this->assertDatabaseHas('parent_student', [
            'parent_id'    => $parentAyah->id,
            'student_id'   => $student->id,
            'relationship' => 'Ayah',
        ]);
    }

    public function test_operator_can_delete_parent_account(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
            'is_active'            => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'parent',
            'name'      => 'Bapak Hapus',
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role'      => 'student',
        ]);

        // Hubungkan ke siswa untuk memastikan pivot terhapus
        $parent->students()->attach($student->id, ['relationship' => 'Ayah']);

        $response = $this->actingAs($operator)->delete(route('operator.parents.destroy', $parent->id));

        $response->assertRedirect(route('operator.parents.index'));
        $response->assertSessionHas('success');

        // Pastikan user terhapus
        $this->assertDatabaseMissing('users', [
            'id' => $parent->id,
        ]);

        // Pastikan relasi pivot terhapus
        $this->assertDatabaseMissing('parent_student', [
            'parent_id' => $parent->id,
        ]);
    }
}
