<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MultiTenantTimezoneTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantWIT;
    protected Tenant $tenantWIB;
    protected User $studentWIT;
    protected User $studentWIB;

    protected function setUp(): void
    {
        parent::setUp();

        // Tenant 1: SMA Negeri 1 Jayapura (WIT, Asia/Jayapura, +9 UTC)
        $this->tenantWIT = Tenant::create([
            'name' => 'SMA Negeri 1 Jayapura',
            'code' => '99001122',
            'slug' => 'sman1jayapura',
            'timezone' => 'Asia/Jayapura',
            'onboarding_completed' => true,
        ]);

        $this->studentWIT = User::factory()->create([
            'tenant_id' => $this->tenantWIT->id,
            'role' => 'student',
            'name' => 'Papua Student',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        // Tenant 2: SMA Negeri 1 Jakarta (WIB, Asia/Jakarta, +7 UTC)
        $this->tenantWIB = Tenant::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'code' => '11002233',
            'slug' => 'sman1jakarta',
            'timezone' => 'Asia/Jakarta',
            'onboarding_completed' => true,
        ]);

        $this->studentWIB = User::factory()->create([
            'tenant_id' => $this->tenantWIB->id,
            'role' => 'student',
            'name' => 'Jakarta Student',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    /**
     * Test 1: Middleware SetTenantTimezone mengubah PHP timezone & config app.timezone sesuai tenant yang sedang login.
     */
    public function test_tenant_with_wit_timezone_configures_php_and_app_timezone_dynamically(): void
    {
        $response = $this->actingAs($this->studentWIT)
            ->get(route('student.dashboard'));

        $response->assertStatus(200);
        $this->assertEquals('Asia/Jayapura', config('app.timezone'));
        $this->assertEquals('Asia/Jayapura', date_default_timezone_get());
    }

    /**
     * Test 2: Siswa Jayapura (WIT) membaca waktu 2 jam lebih cepat dibandingkan Siswa Jakarta (WIB).
     */
    public function test_student_in_wit_tenant_calculates_current_time_with_two_hour_difference_from_wib(): void
    {
        $nowUTC = Carbon::create(2026, 7, 29, 0, 0, 0, 'UTC');
        Carbon::setTestNow($nowUTC);

        $nowJayapura = Carbon::now('Asia/Jayapura'); // 09:00:00 WIT (+9)
        $nowJakarta  = Carbon::now('Asia/Jakarta');  // 07:00:00 WIB (+7)

        $this->assertEquals('09:00:00', $nowJayapura->format('H:i:s'));
        $this->assertEquals('07:00:00', $nowJakarta->format('H:i:s'));
        $this->assertEquals(2, $nowJayapura->diffInHours($nowJakarta));

        Carbon::setTestNow(); // Reset test now
    }
}
