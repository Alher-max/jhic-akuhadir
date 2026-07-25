<?php

namespace Tests\Feature;

use App\Models\ClassSchedule;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClassScheduleTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $user;
    protected SchoolClass $schoolClass;
    protected Subject $subject;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup tenant, user operator/admin, class, & subject
        $this->tenant = Tenant::create([
            'name' => 'SD Negeri 1 Test',
            'institution_type' => 'school',
            'slug' => 'sdn-1-test',
            'code' => 'SD1TEST',
            'onboarding_completed' => true,
            'onboarding_step' => 4,
        ]);

        $this->user = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenant->id,
            'jenjang' => 'SD',
            'tingkat' => 1,
            'nama_kelas' => '1-A',
            'wali_kelas_id' => $this->user->id,
        ]);

        $this->subject = Subject::create([
            'tenant_id' => $this->tenant->id,
            'code' => 'MTK',
            'name' => 'Matematika',
        ]);
    }

    /**
     * Test a: User dapat membuat jadwal KBM dengan data valid.
     */
    public function test_user_can_create_class_schedule_with_valid_data(): void
    {
        $response = $this->actingAs($this->user)->post(route('class-schedules.store'), [
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('class_schedules', [
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);
    }

    /**
     * Test b: User tidak dapat membuat jadwal jika end_time <= start_time.
     */
    public function test_cannot_create_schedule_when_end_time_is_before_or_equal_to_start_time(): void
    {
        $response = $this->actingAs($this->user)->post(route('class-schedules.store'), [
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '08:20',
            'end_time' => '08:15',
        ]);

        $response->assertSessionHasErrors(['end_time']);

        $this->assertDatabaseMissing('class_schedules', [
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'period_number' => 1,
        ]);
    }

    /**
     * Test c: User dapat memperbarui jadwal KBM (Edit).
     */
    public function test_user_can_update_class_schedule(): void
    {
        $schedule = ClassSchedule::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        $response = $this->actingAs($this->user)->post(route('class-schedules.store'), [
            'schedule_id' => $schedule->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '08:00',
            'end_time' => '08:45',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Jadwal pelajaran berhasil diperbarui.');

        $this->assertDatabaseHas('class_schedules', [
            'id' => $schedule->id,
            'start_time' => '08:00',
            'end_time' => '08:45',
        ]);
    }

    /**
     * Test d: User dapat menghapus jadwal KBM (Delete).
     */
    public function test_user_can_delete_class_schedule(): void
    {
        $schedule = ClassSchedule::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        $response = $this->actingAs($this->user)->delete(route('class-schedules.destroy', $schedule->id));

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Jadwal pelajaran berhasil dihapus.');

        $this->assertDatabaseMissing('class_schedules', [
            'id' => $schedule->id,
        ]);
    }

    /**
     * Test e: Prevents creating duplicate schedule for the same class and period.
     */
    public function test_prevents_creating_duplicate_schedule_for_the_same_class_and_period(): void
    {
        // 1. Buat jadwal pertama
        ClassSchedule::create([
            'tenant_id' => $this->tenant->id,
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '07:30',
            'end_time' => '08:15',
        ]);

        // 2. Coba buat jadwal kedua pada kelas, hari, dan period_number yang sama
        $response = $this->actingAs($this->user)->post(route('class-schedules.store'), [
            'class_id' => $this->schoolClass->id,
            'subject_id' => $this->subject->id,
            'teacher_id' => $this->user->id,
            'day_name' => 'Senin',
            'period_number' => 1,
            'start_time' => '08:15',
            'end_time' => '09:00',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'Jadwal untuk kelas pada hari dan jam tersebut sudah ada!');

        // 3. Pastikan jumlah jadwal untuk slot tersebut tetap 1
        $this->assertEquals(1, ClassSchedule::where('class_id', $this->schoolClass->id)
            ->where('day_name', 'Senin')
            ->where('period_number', 1)
            ->count());
    }
}
