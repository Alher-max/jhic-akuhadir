<?php

namespace Tests\Feature;

use App\Models\ActivitySchedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $tenant;
    protected $operator;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a tenant
        $this->tenant = Tenant::factory()->create([
            'name' => 'Test School',
            'subdomain' => 'test-school',
            'onboarding_completed' => true
        ]);

        // Create an operator for the tenant
        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true
        ]);
    }

    /** @test */
    public function test_operator_can_view_activities_page()
    {
        $response = $this->actingAs($this->operator)
            ->get(route('class-schedules.index', ['tab' => 'activities']));

        $response->assertStatus(200);
        // Using assertSee with false for escaping since & is encoded
        $response->assertSee('Ekstrakurikuler & Kegiatan Rutin', false);
    }

    /** @test */
    public function test_operator_can_create_activity()
    {
        $data = [
            'name' => 'Pramuka Wajib',
            'day_name' => 'Sabtu',
            'start_time' => '14:00',
            'end_time' => '16:00',
            'late_tolerance_minutes' => 15,
            'target_scope' => 'all',
        ];

        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('activity_schedules', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Pramuka Wajib',
            'day_name' => 'Sabtu',
            'created_by' => $this->operator->id
        ]);
    }

    /** @test */
    public function test_operator_can_update_activity()
    {
        $activity = ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
            'name' => 'English Club',
            'day_name' => 'Jumat',
            'start_time' => '13:00',
            'end_time' => '14:00',
            'late_tolerance_minutes' => 10,
            'target_scope' => 'member'
        ]);

        $data = [
            'activity_id' => $activity->id,
            'name' => 'English Club Updated',
            'day_name' => 'Jumat',
            'start_time' => '13:30',
            'end_time' => '14:30',
            'late_tolerance_minutes' => 20,
            'target_scope' => 'all'
        ];

        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), $data);

        $response->assertRedirect();
        $this->assertDatabaseHas('activity_schedules', [
            'id' => $activity->id,
            'name' => 'English Club Updated',
            'start_time' => '13:30'
        ]);
    }

    /** @test */
    public function test_operator_can_delete_activity()
    {
        $activity = ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
            'name' => 'Karate',
            'day_name' => 'Kamis',
            'start_time' => '15:00',
            'end_time' => '17:00'
        ]);

        $response = $this->actingAs($this->operator)
            ->delete(route('activities.destroy', $activity->id));

        $response->assertRedirect();
        $this->assertDatabaseMissing('activity_schedules', ['id' => $activity->id]);
    }

    /** @test */
    public function test_unauthorized_user_cannot_delete_activity()
    {
        $activity = ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
            'name' => 'Futsal',
            'day_name' => 'Selasa',
            'start_time' => '16:00',
            'end_time' => '18:00'
        ]);

        // Student role
        $student = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'student',
            'is_active' => true
        ]);

        $response = $this->actingAs($student)
            ->delete(route('activities.destroy', $activity->id));

        // Returns 403 Forbidden
        $response->assertStatus(403);
        $this->assertDatabaseHas('activity_schedules', ['id' => $activity->id]);
    }
}
