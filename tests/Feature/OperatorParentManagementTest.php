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

        $this->assertTrue($parent->fresh()->must_change_password);
        $this->assertFalse($parent->fresh()->is_password_changed);
        $this->assertTrue(Hash::check('081234567890', $parent->fresh()->password));
    }
    public function test_operator_can_create_new_parent_account(): void
    {
        $tenant = $this->createTenant();

        $operator = User::factory()->create([
            'tenant_id'            => $tenant->id,
            'role'                 => 'operator',
            'onboarding_completed' => true,
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
}
