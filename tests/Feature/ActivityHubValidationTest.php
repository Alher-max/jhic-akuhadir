<?php

namespace Tests\Feature;

use App\Models\ActivitySchedule;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityHubValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected SchoolClass $class1;
    protected SchoolClass $class2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Yogyakarta',
            'code' => '20261001',
            'slug' => 'sman1jogja',
            'onboarding_completed' => true,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->class1 = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 1',
        ]);

        $this->class2 = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'nama_kelas' => 'X IPA 2',
        ]);
    }

    /**
     * Test: Berhasil membuat kegiatan baru dengan target_scope = 'all' dan hari yang dipilih.
     */
    public function test_can_store_activity_with_day_and_target_scope_all(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'name' => 'Upacara Bendera',
                'day_name' => 'Senin',
                'start_time' => '07:00',
                'end_time' => '08:00',
                'late_tolerance_minutes' => 10,
                'target_scope' => 'all',
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));
        $this->assertDatabaseHas('activity_schedules', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Upacara Bendera',
            'day_name' => 'Senin',
            'target_scope' => 'all',
        ]);
    }

    /**
     * Test: Berhasil membuat kegiatan baru dengan target_scope = 'class' dan menyertakan target_class_ids.
     */
    public function test_can_store_activity_with_target_scope_class_and_specific_class_ids(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'name' => 'Pramuka Wajib Kelas 10',
                'day_name' => 'Jumat',
                'start_time' => '15:00',
                'end_time' => '17:00',
                'late_tolerance_minutes' => 15,
                'target_scope' => 'class',
                'target_class_ids' => [$this->class1->id, $this->class2->id],
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));

        $activity = ActivitySchedule::where('name', 'Pramuka Wajib Kelas 10')->first();
        $this->assertNotNull($activity);
        $this->assertEquals('Jumat', $activity->day_name);
        $this->assertEquals('class', $activity->target_scope);
        $this->assertEquals([$this->class1->id, $this->class2->id], $activity->target_class_ids);
    }

    /**
     * Test: Berhasil membuat kegiatan baru dengan target_scope = 'members' (Anggota Ekskul).
     */
    public function test_can_store_activity_with_target_scope_members(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'name' => 'Latihan PMR',
                'day_name' => 'Sabtu',
                'start_time' => '15:00',
                'end_time' => '16:30',
                'late_tolerance_minutes' => 15,
                'target_scope' => 'members',
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));
        $this->assertDatabaseHas('activity_schedules', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Latihan PMR',
            'day_name' => 'Sabtu',
            'target_scope' => 'members',
        ]);
    }

    /**
     * Test: Gagal membuat kegiatan tanpa mengisi field 'day_name'.
     */
    public function test_cannot_store_activity_without_required_day_name(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'name' => 'Kegiatan Tanpa Hari',
                'start_time' => '07:00',
                'end_time' => '08:00',
                'target_scope' => 'all',
            ]);

        $response->assertSessionHasErrors(['day_name']);
    }

    /**
     * Test: Gagal membuat kegiatan jika target_scope bernilai tidak valid.
     */
    public function test_cannot_store_activity_with_invalid_target_scope(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'name' => 'Kegiatan Scope Ilegal',
                'day_name' => 'Rabu',
                'start_time' => '07:00',
                'end_time' => '08:00',
                'target_scope' => 'invalid_scope',
            ]);

        $response->assertSessionHasErrors(['target_scope']);
    }

    /**
     * Test: Berhasil meng-update kegiatan yang sudah ada.
     */
    public function test_can_update_existing_activity_with_new_day_and_scope(): void
    {
        $activity = ActivitySchedule::create([
            'tenant_id' => $this->tenant->id,
            'created_by' => $this->operator->id,
            'name' => 'Senam Pagi',
            'day_name' => 'Jumat',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'late_tolerance_minutes' => 10,
            'target_scope' => 'all',
        ]);

        $response = $this->actingAs($this->operator)
            ->post(route('activities.store'), [
                'activity_id' => $activity->id,
                'name' => 'Senam & Olahraga Bersama',
                'day_name' => 'Sabtu',
                'start_time' => '06:30',
                'end_time' => '07:30',
                'late_tolerance_minutes' => 15,
                'target_scope' => 'class',
                'target_class_ids' => [$this->class1->id],
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'activities']));

        $activity->refresh();
        $this->assertEquals('Senam & Olahraga Bersama', $activity->name);
        $this->assertEquals('Sabtu', $activity->day_name);
        $this->assertEquals('class', $activity->target_scope);
        $this->assertEquals([$this->class1->id], $activity->target_class_ids);
    }
}
