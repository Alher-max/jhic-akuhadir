<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\Announcement;

class HomeroomAnnouncementTest extends TestCase
{
    use RefreshDatabase;

    public function test_homeroom_teacher_can_access_announcements()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'code' => 'TEST', 'slug' => 'test-tenant']);
        $teacher = User::factory()->create(['role' => 'teacher', 'tenant_id' => $tenant->id]);
        SchoolClass::create(['nama_kelas' => 'Kelas X', 'jenjang' => 'SMA', 'tingkat' => 10, 'wali_kelas_id' => $teacher->id, 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($teacher->fresh())->get(route('teacher.announcements.index'));

        $response->assertStatus(200);
    }

    public function test_regular_teacher_cannot_access_announcements()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'code' => 'TEST', 'slug' => 'test-tenant']);
        $teacher = User::factory()->create(['role' => 'teacher', 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($teacher->fresh())->get(route('teacher.announcements.index'));

        $response->assertStatus(403);
    }

    public function test_homeroom_teacher_can_create_announcement()
    {
        $tenant = Tenant::create(['name' => 'Test Tenant', 'code' => 'TEST', 'slug' => 'test-tenant']);
        $teacher = User::factory()->create(['role' => 'teacher', 'tenant_id' => $tenant->id]);
        $class = SchoolClass::create(['nama_kelas' => 'Kelas X', 'jenjang' => 'SMA', 'tingkat' => 10, 'wali_kelas_id' => $teacher->id, 'tenant_id' => $tenant->id]);

        $response = $this->actingAs($teacher->fresh())->post(route('teacher.announcements.store'), [
            'school_class_id' => $class->id,
            'title' => 'Pengumuman Penting',
            'description' => 'Ini adalah deskripsi pengumuman.',
            'target_audience' => 'both',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('announcements', [
            'title' => 'Pengumuman Penting',
            'school_class_id' => $class->id,
        ]);
    }
}
