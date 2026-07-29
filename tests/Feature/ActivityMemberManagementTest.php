<?php

namespace Tests\Feature;

use App\Models\ActivitySchedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityMemberManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected User $student1;
    protected User $student2;
    protected ActivitySchedule $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Surabaya',
            'code' => '20263001',
            'slug' => 'sman1sub',
            'onboarding_completed' => true,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->student1 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'name' => 'Ahmad Dahlan',
            'is_active' => true,
        ]);

        $this->student2 = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'name' => 'Siti Nurhaliza',
            'is_active' => true,
        ]);

        $this->activity = ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
            'name' => 'Ekskul Robotik',
            'day_name' => 'Sabtu',
            'start_time' => '15:00:00',
            'end_time' => '17:00:00',
            'target_scope' => 'members',
        ]);
    }

    /**
     * Test: Operator dapat meng-update (sync) daftar anggota siswa pada kegiatan ekskul.
     */
    public function test_can_sync_members_to_an_activity(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.members.update', $this->activity->id), [
                'student_ids' => [$this->student1->id, $this->student2->id],
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));

        $this->assertDatabaseHas('activity_members', [
            'activity_schedule_id' => $this->activity->id,
            'student_id' => $this->student1->id,
        ]);
        $this->assertDatabaseHas('activity_members', [
            'activity_schedule_id' => $this->activity->id,
            'student_id' => $this->student2->id,
        ]);
        $this->assertEquals(2, $this->activity->members()->count());
    }

    /**
     * Test: Operator dapat menambahkan satu siswa ke dalam kegiatan ekskul.
     */
    public function test_can_add_single_student_to_an_activity(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.members.add', $this->activity->id), [
                'student_id' => $this->student1->id,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('activity_members', [
            'activity_schedule_id' => $this->activity->id,
            'student_id' => $this->student1->id,
        ]);
    }

    /**
     * Test: Operator dapat menghapus siswa dari anggota kegiatan ekskul.
     */
    public function test_can_remove_student_from_an_activity(): void
    {
        $this->activity->members()->attach($this->student1->id);

        $response = $this->actingAs($this->operator)
            ->delete(route('activities.members.remove', [$this->activity->id, $this->student1->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_members', [
            'activity_schedule_id' => $this->activity->id,
            'student_id' => $this->student1->id,
        ]);
    }

    /**
     * Test: Tidak dapat menambahkan siswa dari tenant lain ke dalam kegiatan ekskul.
     */
    public function test_cannot_add_student_from_different_tenant_to_activity(): void
    {
        $otherTenant = Tenant::create([
            'name' => 'SMA Negeri 2 Surabaya',
            'code' => '20263002',
            'slug' => 'sman2sub',
            'onboarding_completed' => true,
        ]);

        $foreignStudent = User::factory()->create([
            'tenant_id' => $otherTenant->id,
            'role' => 'student',
            'name' => 'Siswa Asing',
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('activities.members.update', $this->activity->id), [
                'student_ids' => [$foreignStudent->id],
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));
        $this->assertDatabaseMissing('activity_members', [
            'activity_schedule_id' => $this->activity->id,
            'student_id' => $foreignStudent->id,
        ]);
    }
}
