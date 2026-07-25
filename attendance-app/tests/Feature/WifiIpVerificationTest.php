<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WifiIpVerificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_pwa_clock_in_captures_client_ip_and_sets_wifi_verified(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $setting = AttendanceSetting::create([
            'tenant_id' => $tenant->id,
            'method_pwa' => true,
            'latitude' => -7.7972,
            'longitude' => 110.3688,
            'radius_meters' => 100,
            'biometric_ip_address' => '203.0.113.50, 198.51.100.1',
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Wi-Fi Test',
            'email' => 'siswawifi@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'name' => 'Jadwal Regular',
            'type' => 'routine',
            'day_of_week' => now()->format('N'),
            'start_time' => '06:00:00',
            'end_time' => '15:00:00',
            'grace_period_minutes' => 60,
        ]);
        $student->schedules()->attach($schedule);

        // Clock in from registered Wi-Fi IP (203.0.113.50)
        $response = $this->actingAs($student)
            ->withServerVariables(['REMOTE_ADDR' => '203.0.113.50'])
            ->postJson(route('pwa.store'), [
                'latitude' => -7.7972,
                'longitude' => 110.3688,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('user_id', $student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('203.0.113.50', $attendance->ip_address);
        $this->assertTrue((bool) $attendance->is_wifi_verified);
    }

    public function test_pwa_clock_in_flexible_mode_allows_cellular_ip(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Yogyakarta',
            'slug' => 'sman2yogyakarta',
            'code' => 'SMAN2YOGYA',
            'subdomain' => 'sman2yogyakarta',
            'onboarding_completed' => true,
        ]);

        $setting = AttendanceSetting::create([
            'tenant_id' => $tenant->id,
            'method_pwa' => true,
            'latitude' => -7.7972,
            'longitude' => 110.3688,
            'radius_meters' => 100,
            'biometric_ip_address' => '203.0.113.50',
        ]);

        $student = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa Cellular Test',
            'email' => 'siswacell@sman2yogyakarta.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $schedule = Schedule::create([
            'user_id' => $student->id,
            'tenant_id' => $tenant->id,
            'name' => 'Jadwal Regular',
            'type' => 'routine',
            'day_of_week' => now()->format('N'),
            'start_time' => '06:00:00',
            'end_time' => '15:00:00',
            'grace_period_minutes' => 60,
        ]);
        $student->schedules()->attach($schedule);

        // Clock in from cellular IP (182.253.10.20)
        $response = $this->actingAs($student)
            ->withServerVariables(['REMOTE_ADDR' => '182.253.10.20'])
            ->postJson(route('pwa.store'), [
                'latitude' => -7.7972,
                'longitude' => 110.3688,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $attendance = Attendance::where('user_id', $student->id)->first();
        $this->assertNotNull($attendance);
        $this->assertEquals('182.253.10.20', $attendance->ip_address);
        $this->assertFalse((bool) $attendance->is_wifi_verified);
    }
}
