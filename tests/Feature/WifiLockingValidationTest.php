<?php

namespace Tests\Feature;

use App\Models\AttendanceSetting;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WifiLockingValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_wifi_locking_validation_succeeds_when_bssid_and_mac_are_empty(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Testing',
            'slug' => 'sman1testing',
            'code' => 'SMAN1TST',
            'subdomain' => 'sman1testing',
            'onboarding_completed' => true,
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Sekolah',
            'email' => 'operator@sman1testing.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        // Submit form pengaturan absensi dengan wifi_allowed_macs dikosongkan (empty / nullable)
        $response = $this->actingAs($operator)
            ->put(route('attendance-settings.update'), [
                'method_pwa' => '1',
                'method_wifi' => '1',
                'biometric_ip_address' => '180.252.10.1',
                'wifi_allowed_ssids' => 'Wi-Fi_Sekolah_1',
                'wifi_allowed_macs' => '', // Dikosongkan
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $setting = AttendanceSetting::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($setting);
        $this->assertTrue((bool) $setting->method_wifi);
        $this->assertEmpty($setting->wifi_allowed_macs);
    }

    public function test_onboarding_wifi_method_succeeds_when_bssid_is_empty(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 2 Testing',
            'slug' => 'sman2testing',
            'code' => 'SMAN2TST',
            'subdomain' => 'sman2testing',
            'onboarding_completed' => false,
        ]);

        $admin = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Sekolah 2',
            'email' => 'operator@sman2testing.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        // Submit form onboarding dengan attendance_method wifi dan wifi_bssid dikosongkan
        $response = $this->actingAs($admin)
            ->post(route('admin.onboarding'), [
                'attendance_method' => 'wifi',
                'wifi_bssid' => '', // Dikosongkan
            ]);

        $response->assertRedirect(route('dashboard'));
        $response->assertSessionHasNoErrors();

        $this->assertTrue((bool) $tenant->fresh()->onboarding_completed);
        $this->assertEmpty($tenant->fresh()->wifi_bssid);
    }
}
