<?php

namespace Tests\Feature;

use App\Models\AttendanceSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceSettingLivenessTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_toggle_liveness_detection_off(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Sekolah',
            'email' => 'operator@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        $setting = AttendanceSetting::create([
            'tenant_id' => $tenant->id,
            'is_liveness_active' => true,
            'method_pwa' => true,
        ]);

        // Submit form with is_liveness_active unchecked (omitted from request body)
        $response = $this->from(route('attendance-settings.index'))
            ->actingAs($operator)
            ->put(route('attendance-settings.update'), [
                'method_pwa' => '1',
                // is_liveness_active is unchecked, so omitted
            ]);

        $response->assertRedirect(route('attendance-settings.index'));
        $response->assertSessionHas('success');

        $this->assertFalse((bool) $setting->fresh()->is_liveness_active);
    }
}
