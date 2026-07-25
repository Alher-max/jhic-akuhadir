<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendanceSetting;
use App\Models\Schedule;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiWifiIpSettingTest extends TestCase
{
    use RefreshDatabase;

    public function test_operator_can_save_multi_ip_wifi_and_students_verify_correctly(): void
    {
        $tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Semarang',
            'slug' => 'sman1semarang',
            'code' => 'SMAN1SMG',
            'subdomain' => 'sman1semarang',
            'onboarding_completed' => true,
        ]);

        $operator = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Operator Sekolah',
            'email' => 'operator@sman1semarang.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        $studentA = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa A (Wi-Fi)',
            'email' => 'siswaa@sman1semarang.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        $studentB = User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Siswa B (Seluler)',
            'email' => 'siswab@sman1semarang.sch.id',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'role' => 'student',
            'is_active' => true,
        ]);

        // Attach routine schedule
        foreach ([$studentA, $studentB] as $std) {
            $sch = Schedule::create([
                'user_id' => $std->id,
                'tenant_id' => $tenant->id,
                'name' => 'Jadwal Pagi',
                'type' => 'routine',
                'day_of_week' => now()->format('N'),
                'start_time' => '06:00:00',
                'end_time' => '15:00:00',
                'grace_period_minutes' => 60,
            ]);
            $std->schedules()->attach($sch);
        }

        // STEP 1: Operator menyimpan 3 IP Wi-Fi (dengan spasi acak untuk di-trim sanitasi)
        $responseSave = $this->actingAs($operator)
            ->put(route('attendance-settings.update'), [
                'method_pwa' => '1',
                'method_wifi' => '1',
                'biometric_ip_address' => ' 1.1.1.1,  2.2.2.2 , 3.3.3.3 ',
            ]);

        $responseSave->assertRedirect();

        $setting = AttendanceSetting::where('tenant_id', $tenant->id)->first();
        $this->assertNotNull($setting);
        $this->assertEquals('1.1.1.1, 2.2.2.2, 3.3.3.3', $setting->biometric_ip_address);

        // STEP 2: Siswa A clock-in dari IP 2.2.2.2 -> is_wifi_verified MUST be true
        $responseA = $this->actingAs($studentA)
            ->withServerVariables(['REMOTE_ADDR' => '2.2.2.2'])
            ->postJson(route('pwa.store'));

        $responseA->assertStatus(200);
        $responseA->assertJson(['success' => true]);

        $attA = Attendance::where('user_id', $studentA->id)->first();
        $this->assertNotNull($attA);
        $this->assertEquals('2.2.2.2', $attA->ip_address);
        $this->assertTrue((bool) $attA->is_wifi_verified);

        // STEP 3: Siswa B clock-in dari IP 8.8.8.8 -> is_wifi_verified MUST be false
        $responseB = $this->actingAs($studentB)
            ->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
            ->postJson(route('pwa.store'));

        $responseB->assertStatus(200);
        $responseB->assertJson(['success' => true]);

        $attB = Attendance::where('user_id', $studentB->id)->first();
        $this->assertNotNull($attB);
        $this->assertEquals('8.8.8.8', $attB->ip_address);
        $this->assertFalse((bool) $attB->is_wifi_verified);
    }
}
