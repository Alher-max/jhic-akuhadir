<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ParentStudentRelationTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_and_student_belongs_to_many_relationship(): void
    {
        $tenant = Tenant::create(['name' => 'Test School', 'code' => 'SCH01']);

        $parent = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'parent',
            'name' => 'Ayah Budi',
        ]);

        $student = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'student',
            'name' => 'Budi',
        ]);

        $parent->students()->attach($student->id, ['relationship' => 'Ayah']);

        $this->assertCount(1, $parent->fresh()->students);
        $this->assertEquals('Budi', $parent->fresh()->students->first()->name);
        $this->assertEquals('Ayah', $parent->fresh()->students->first()->pivot->relationship);

        $this->assertCount(1, $student->fresh()->parents);
        $this->assertEquals('Ayah Budi', $student->fresh()->parents->first()->name);
    }

    public function test_operator_can_access_parent_management_index_page(): void
    {
        $tenant = Tenant::create(['name' => 'Test School', 'code' => 'SCH01']);

        $operator = User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'operator',
            'onboarding_completed' => true,
        ]);

        $response = $this->actingAs($operator)->get(route('operator.parents.index'));
        $response->assertStatus(200);
        $response->assertSee('Daftar Orang Tua');
    }
}
