<?php

namespace Tests\Feature;

use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorTeacherManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_view_teachers_list(): void
    {
        $tenant = Tenant::create(['name' => 'SMK Garuda', 'code' => 'SMKGAR', 'slug' => 'smk-garuda']);
        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Admin',
            'email' => 'operator@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Budi Santoso, S.Pd.',
            'email' => 'budi@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'nisn' => '198501012010011001',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($operator)->get(route('operator.teachers.index'));

        $response->assertStatus(200);
        $response->assertSee('Budi Santoso, S.Pd.');
    }

    public function test_newly_registered_teacher_without_class_is_visible_to_operator(): void
    {
        $tenant = Tenant::create(['name' => 'SMK Garuda', 'code' => 'SMKGAR', 'slug' => 'smk-garuda']);
        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Admin',
            'email' => 'operator@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        // Guru baru registered with role = 'teacher', no assigned class
        $newTeacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siti Nurhaliza, M.Pd.',
            'email' => 'siti.new@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($operator)->get(route('operator.teachers.index'));

        $response->assertStatus(200);
        $response->assertSee('Siti Nurhaliza, M.Pd.');
    }

    public function test_operator_can_update_teacher_information_and_homeroom_assignment(): void
    {
        $tenant = Tenant::create(['name' => 'SMK Garuda', 'code' => 'SMKGAR', 'slug' => 'smk-garuda']);
        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Admin',
            'email' => 'operator@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $teacher = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Budi Santoso, S.Pd.',
            'email' => 'budi@smkgaruda.sch.id',
            'password' => bcrypt('password'),
            'role' => 'guru',
            'nisn' => '198501012010011001',
            'is_active' => true,
            'onboarding_completed' => true,
            'email_verified_at' => now(),
        ]);

        $schoolClass = SchoolClass::create([
            'tenant_id' => $tenant->id,
            'jenjang' => 'SMK',
            'tingkat' => '10',
            'nama_kelas' => 'X RPL 1',
        ]);

        $response = $this->actingAs($operator)
            ->put(route('operator.teachers.update', $teacher->id), [
                'name' => 'Drs. Budi Santoso, M.Pd.',
                'email' => 'budi@smkgaruda.sch.id',
                'nip' => '198501012010011001',
                'role' => 'wali_kelas',
                'is_active' => 1,
                'class_id' => $schoolClass->id,
            ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect();

        $teacher->refresh();
        $this->assertEquals('Drs. Budi Santoso, M.Pd.', $teacher->name);
        $this->assertEquals('wali_kelas', $teacher->role);

        $schoolClass->refresh();
        $this->assertEquals($teacher->id, $schoolClass->wali_kelas_id);
    }
}
