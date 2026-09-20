<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\Announcement;

class AnnouncementVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_see_all_and_student_announcements()
    {
        $tenant = Tenant::create(['name' => 'School', 'code' => 'SCH', 'slug' => 'school']);
        $class = SchoolClass::create(['nama_kelas' => 'X', 'jenjang' => 'SMA', 'tingkat' => 10, 'tenant_id' => $tenant->id]);
        $student = User::factory()->create(['role' => 'student', 'class_id' => $class->id, 'tenant_id' => $tenant->id, 'is_active' => true]);
        $teacher = $this->createTeacher($tenant);

        $announcementAll = Announcement::create(['school_class_id' => $class->id, 'title' => 'All', 'description' => 'Desc', 'target_audience' => 'both', 'teacher_id' => $teacher->id]);
        $announcementStudent = Announcement::create(['school_class_id' => $class->id, 'title' => 'Student', 'description' => 'Desc', 'target_audience' => 'students', 'teacher_id' => $teacher->id]);
        $announcementParent = Announcement::create(['school_class_id' => $class->id, 'title' => 'Parent', 'description' => 'Desc', 'target_audience' => 'parents', 'teacher_id' => $teacher->id]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('All');
        $response->assertSee('Student');
        $response->assertDontSee('Parent');
    }

    public function test_parent_can_see_all_and_parent_announcements()
    {
        $tenant = Tenant::create(['name' => 'School', 'code' => 'SCH', 'slug' => 'school']);
        $class = SchoolClass::create(['nama_kelas' => 'X', 'jenjang' => 'SMA', 'tingkat' => 10, 'tenant_id' => $tenant->id]);
        $student = User::factory()->create(['role' => 'student', 'class_id' => $class->id, 'tenant_id' => $tenant->id, 'is_active' => true]);
        $parent = User::factory()->create(['role' => 'parent', 'tenant_id' => $tenant->id, 'is_active' => true]);
        $teacher = $this->createTeacher($tenant);
        $parent->students()->attach($student->id, ['relationship' => 'Orang Tua']);

        $announcementAll = Announcement::create(['school_class_id' => $class->id, 'title' => 'All', 'description' => 'Desc', 'target_audience' => 'both', 'teacher_id' => $teacher->id]);
        $announcementStudent = Announcement::create(['school_class_id' => $class->id, 'title' => 'Student', 'description' => 'Desc', 'target_audience' => 'students', 'teacher_id' => $teacher->id]);
        $announcementParent = Announcement::create(['school_class_id' => $class->id, 'title' => 'Parent', 'description' => 'Desc', 'target_audience' => 'parents', 'teacher_id' => $teacher->id]);

        $response = $this->actingAs($parent)->get(route('parent.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('All');
        $response->assertSee('Parent');
        $response->assertDontSee('Student');
    }

    public function test_announcement_isolation_by_class()
    {
        $tenant = Tenant::create(['name' => 'School', 'code' => 'SCH', 'slug' => 'school']);
        $class1 = SchoolClass::create(['nama_kelas' => 'X', 'jenjang' => 'SMA', 'tingkat' => 10, 'tenant_id' => $tenant->id]);
        $class2 = SchoolClass::create(['nama_kelas' => 'Y', 'jenjang' => 'SMA', 'tingkat' => 11, 'tenant_id' => $tenant->id]);
        
        $student = User::factory()->create(['role' => 'student', 'class_id' => $class1->id, 'tenant_id' => $tenant->id, 'is_active' => true]);
        $teacher = $this->createTeacher($tenant);
        
        $announcementClass2 = Announcement::create(['school_class_id' => $class2->id, 'title' => 'Class2', 'description' => 'Desc', 'target_audience' => 'both', 'teacher_id' => $teacher->id]);

        $response = $this->actingAs($student)->get(route('student.dashboard'));
        $response->assertStatus(200);
        $response->assertDontSee('Class2');
    }

    private function createTeacher(Tenant $tenant): User
    {
        return User::factory()->create([
            'tenant_id' => $tenant->id,
            'role' => 'teacher',
            'is_active' => true,
        ]);
    }
}
