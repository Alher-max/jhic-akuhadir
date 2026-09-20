<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OperatorAttendanceScanApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_scan_requires_authentication(): void
    {
        $this->postJson('/api/v1/attendance/scan', ['code' => '123'])->assertUnauthorized();
    }

    public function test_scan_requires_operator_role(): void
    {
        $tenant = $this->tenant('school');
        $teacher = $this->user($tenant, 'teacher');

        $this->actingAs($teacher)->postJson('/api/v1/attendance/scan', ['code' => '123'])
            ->assertForbidden();
    }

    public function test_scan_resolves_student_by_nisn_and_records_audit_and_status(): void
    {
        $tenant = $this->tenant('school');
        $operator = $this->user($tenant, 'operator');
        $student = $this->user($tenant, 'student', ['nisn' => '0011223344']);

        $response = $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => $student->nisn]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('student.id', $student->id)
            ->assertJsonStructure(['student' => ['id', 'name', 'nisn', 'class_name', 'avatar_url'], 'status', 'time']);
        $this->assertContains($response->json('status'), ['present', 'late']);
        $this->assertDatabaseHas('attendances', [
            'tenant_id' => $tenant->id,
            'user_id' => $student->id,
            'recorded_by_user_id' => $operator->id,
        ]);
        $this->assertContains(Attendance::first()->status, ['present', 'late']);
    }

    public function test_scan_supports_nis_and_std_id_but_does_not_cross_tenants(): void
    {
        $tenant = $this->tenant('school');
        $otherTenant = $this->tenant('other-school');
        $operator = $this->user($tenant, 'operator');
        $student = $this->user($tenant, 'student', ['nis' => 'NIS-001']);
        $otherStudent = $this->user($otherTenant, 'student', ['nisn' => 'NISN-OTHER']);

        $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => 'NIS-001'])
            ->assertOk();
        $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => 'STD-' . $otherStudent->id])
            ->assertNotFound();
    }

    public function test_scan_returns_not_found_for_unknown_student_and_is_idempotent(): void
    {
        $tenant = $this->tenant('school');
        $operator = $this->user($tenant, 'operator');
        $student = $this->user($tenant, 'student', ['nisn' => 'NIS-001']);

        $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => 'UNKNOWN'])
            ->assertNotFound();
        $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => $student->nisn])
            ->assertOk();
        $this->actingAs($operator)->postJson('/api/v1/attendance/scan', ['code' => $student->nisn])
            ->assertStatus(409)
            ->assertJsonPath('success', false)
            ->assertJsonPath('already_attended', true);
        $this->assertDatabaseCount('attendances', 1);
    }

    private function user(Tenant $tenant, string $role, array $attributes = []): User
    {
        return User::create(array_merge([
            'tenant_id' => $tenant->id,
            'name' => ucfirst($role),
            'email' => $role . $tenant->id . '@example.test',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => $role,
            'is_active' => true,
        ], $attributes));
    }

    private function tenant(string $slug): Tenant
    {
        return Tenant::create([
            'name' => ucwords(str_replace('-', ' ', $slug)),
            'slug' => $slug,
            'code' => strtoupper(str_replace('-', '', $slug)),
            'onboarding_completed' => true,
            'working_days' => [7],
        ]);
    }
}
