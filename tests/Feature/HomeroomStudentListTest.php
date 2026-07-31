<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;

class HomeroomStudentListTest extends TestCase
{
    use RefreshDatabase;

    public function test_homeroom_teacher_can_access_student_list()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'code' => 'TEST', 'slug' => 'test-tenant']);
        $teacher = User::factory()->create(['role' => 'wali_kelas', 'tenant_id' => $tenant->id]);
        $class = SchoolClass::create(['nama_kelas' => 'Kelas X', 'jenjang' => 'SMA', 'tingkat' => 10, 'wali_kelas_id' => $teacher->id, 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($teacher)->get(route('students.index'));

        $response->assertStatus(200);
    }
}
