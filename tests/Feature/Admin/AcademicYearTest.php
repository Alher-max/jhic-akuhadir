<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicYearTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenantA;
    protected Tenant $tenantB;
    protected User $operatorA;
    protected User $headmasterA;
    protected User $operatorB;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Setup Tenant A & Operator & Headmaster
        $this->tenantA = Tenant::create([
            'name' => 'SMA Negeri 1 Prestasi',
            'slug' => 'sman-1-prestasi',
            'code' => 'SMAN1P',
            'institution_type' => 'school',
            'onboarding_completed' => true,
        ]);

        $this->operatorA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $this->headmasterA = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'headmaster',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // 2. Setup Tenant B & Operator
        $this->tenantB = Tenant::create([
            'name' => 'SMP Negeri 2 Juara',
            'slug' => 'smpn-2-juara',
            'code' => 'SMPN2J',
            'institution_type' => 'school',
            'onboarding_completed' => true,
        ]);

        $this->operatorB = User::factory()->create([
            'tenant_id' => $this->tenantB->id,
            'role' => 'operator',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);
    }

    /**
     * KASUS UJI 1: TENANT ISOLATION TEST
     * User dari Tenant A tidak dapat melihat, mengedit, menghapus, atau mengaktifkan tahun ajaran milik Tenant B.
     */
    public function test_tenant_isolation_on_academic_years(): void
    {
        // Buat data untuk Tenant A
        $yearA = AcademicYear::create([
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        // Buat data untuk Tenant B
        $yearB = AcademicYear::create([
            'tenant_id' => $this->tenantB->id,
            'name' => '2025/2026',
            'semester' => '2',
            'start_date' => '2026-01-05',
            'end_date' => '2026-06-25',
            'is_active' => true,
        ]);

        // 1. Tenant A melihat daftar tahun ajaran: hanya melihat miliknya
        $responseIndex = $this->actingAs($this->operatorA)
            ->get(route('admin.academic-years.index'));

        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('2026/2027');
        $responseIndex->assertDontSee('2025/2026');

        // 2. Operator Tenant A mencoba mengedit/update tahun ajaran Tenant B
        $responseUpdate = $this->actingAs($this->operatorA)
            ->put(route('admin.academic-years.update', $yearB), [
                'name' => '2025/2026 Diubah Tenant A',
                'semester' => '2',
                'start_date' => '2026-01-05',
                'end_date' => '2026-06-25',
                'is_active' => false,
            ]);

        $responseUpdate->assertStatus(404);
        $this->assertDatabaseHas('academic_years', [
            'id' => $yearB->id,
            'name' => '2025/2026',
            'tenant_id' => $this->tenantB->id,
        ]);

        // 3. Operator Tenant A mencoba menghapus tahun ajaran Tenant B
        $responseDelete = $this->actingAs($this->operatorA)
            ->delete(route('admin.academic-years.destroy', $yearB));

        $responseDelete->assertStatus(404);
        $this->assertDatabaseHas('academic_years', [
            'id' => $yearB->id,
            'tenant_id' => $this->tenantB->id,
        ]);

        // 4. Operator Tenant A mencoba mengaktifkan tahun ajaran Tenant B
        $yearBInactive = AcademicYear::create([
            'tenant_id' => $this->tenantB->id,
            'name' => '2027/2028',
            'semester' => '1',
            'start_date' => '2027-07-15',
            'end_date' => '2027-12-20',
            'is_active' => false,
        ]);

        $responseActivate = $this->actingAs($this->operatorA)
            ->patch(route('admin.academic-years.activate', $yearBInactive));

        $responseActivate->assertStatus(404);
        $this->assertFalse((bool) $yearBInactive->fresh()->is_active);
    }

    /**
     * KASUS UJI 2: AUTHORIZATION TEST
     * Role student dan parent ditolak dengan status 403 saat mengakses rute ini;
     * hanya operator dan headmaster yang diizinkan.
     */
    public function test_authorization_for_academic_years(): void
    {
        $year = AcademicYear::create([
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        $student = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'student',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        $parent = User::factory()->create([
            'tenant_id' => $this->tenantA->id,
            'role' => 'parent',
            'is_active' => true,
            'onboarding_completed' => true,
        ]);

        // 1. Student ditolak 403
        $this->actingAs($student)
            ->get(route('admin.academic-years.index'))
            ->assertStatus(403);

        $this->actingAs($student)
            ->post(route('admin.academic-years.store'), [
                'name' => '2027/2028',
                'semester' => '1',
                'start_date' => '2027-07-01',
                'end_date' => '2027-12-31',
            ])
            ->assertStatus(403);

        $this->actingAs($student)
            ->patch(route('admin.academic-years.activate', $year))
            ->assertStatus(403);

        // 2. Parent ditolak 403
        $this->actingAs($parent)
            ->get(route('admin.academic-years.index'))
            ->assertStatus(403);

        $this->actingAs($parent)
            ->delete(route('admin.academic-years.destroy', $year))
            ->assertStatus(403);

        // 3. Operator diizinkan
        $this->actingAs($this->operatorA)
            ->get(route('admin.academic-years.index'))
            ->assertStatus(200);

        // 4. Headmaster diizinkan
        $this->actingAs($this->headmasterA)
            ->get(route('admin.academic-years.index'))
            ->assertStatus(200);
    }

    /**
     * KASUS UJI 3: VALIDATION RULES TEST
     * Gagal menyimpan jika format tanggal tidak valid, jika end_date < start_date,
     * atau jika nama/semester kosong.
     */
    public function test_validation_rules_on_academic_years(): void
    {
        // 1. Nama dan semester kosong
        $responseEmpty = $this->actingAs($this->operatorA)
            ->post(route('admin.academic-years.store'), [
                'name' => '',
                'semester' => '',
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
            ]);

        $responseEmpty->assertSessionHasErrors(['name', 'semester']);

        // 2. Format tanggal tidak valid
        $responseInvalidDate = $this->actingAs($this->operatorA)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'semester' => '1',
                'start_date' => 'tanggal-tidak-valid',
                'end_date' => '2026-12-20',
            ]);

        $responseInvalidDate->assertSessionHasErrors(['start_date']);

        // 3. end_date < start_date (Tanggal selesai lebih awal dari tanggal mulai)
        $responseInvalidRange = $this->actingAs($this->operatorA)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'semester' => '1',
                'start_date' => '2026-12-30',
                'end_date' => '2026-07-15',
            ]);

        $responseInvalidRange->assertSessionHasErrors(['end_date']);

        // 4. Semester bernilai selain 1 atau 2
        $responseInvalidSemester = $this->actingAs($this->operatorA)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'semester' => '3',
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
            ]);

        $responseInvalidSemester->assertSessionHasErrors(['semester']);

        // 5. Data valid berhasil disimpan
        $responseSuccess = $this->actingAs($this->operatorA)
            ->post(route('admin.academic-years.store'), [
                'name' => '2026/2027',
                'semester' => '1',
                'start_date' => '2026-07-15',
                'end_date' => '2026-12-20',
                'is_active' => '1',
            ]);

        $responseSuccess->assertRedirect(route('admin.academic-years.index'));
        $responseSuccess->assertSessionHas('success');
        $this->assertDatabaseHas('academic_years', [
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '1',
            'is_active' => true,
        ]);
    }

    /**
     * KASUS UJI 4: SINGLE ACTIVE YEAR INVARIANT TEST
     * Saat Tahun Ajaran Y diaktifkan, pastikan Tahun Ajaran X yang sebelumnya aktif
     * berubah menjadi is_active = false secara otomatis tanpa memengaruhi tenant lain.
     */
    public function test_single_active_year_invariant(): void
    {
        // Tenant A memiliki Tahun Ajaran X (sedang aktif)
        $yearX = AcademicYear::create([
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        // Tenant A memiliki Tahun Ajaran Y (belum aktif)
        $yearY = AcademicYear::create([
            'tenant_id' => $this->tenantA->id,
            'name' => '2026/2027',
            'semester' => '2',
            'start_date' => '2027-01-05',
            'end_date' => '2027-06-25',
            'is_active' => false,
        ]);

        // Tenant B memiliki Tahun Ajaran Z (sedang aktif di Tenant B)
        $yearZ = AcademicYear::create([
            'tenant_id' => $this->tenantB->id,
            'name' => '2026/2027',
            'semester' => '1',
            'start_date' => '2026-07-15',
            'end_date' => '2026-12-20',
            'is_active' => true,
        ]);

        // Pastikan kondisi awal
        $this->assertTrue((bool) $yearX->fresh()->is_active);
        $this->assertFalse((bool) $yearY->fresh()->is_active);
        $this->assertTrue((bool) $yearZ->fresh()->is_active);

        // Aksi: Operator Tenant A mengaktifkan Tahun Ajaran Y
        $response = $this->actingAs($this->operatorA)
            ->patch(route('admin.academic-years.activate', $yearY));

        $response->assertRedirect(route('admin.academic-years.index'));
        $response->assertSessionHas('success');

        // Verifikasi Invarian:
        // 1. Tahun Y menjadi aktif
        $this->assertTrue((bool) $yearY->fresh()->is_active);

        // 2. Tahun X otomatis menjadi nonaktif
        $this->assertFalse((bool) $yearX->fresh()->is_active);

        // 3. Tahun Z milik Tenant B TIDAK terpengaruh dan tetap aktif
        $this->assertTrue((bool) $yearZ->fresh()->is_active);

        // 4. Pastikan hanya ada 1 tahun ajaran aktif di Tenant A
        $activeCountTenantA = AcademicYear::withoutGlobalScope('tenant')
            ->where('tenant_id', $this->tenantA->id)
            ->where('is_active', true)
            ->count();
        $this->assertSame(1, $activeCountTenantA);
    }

    /**
     * KASUS UJI 5: SCHOOL CLASS FASE & CURRICULUM FIELD TEST
     * Memastikan rombel dapat menyimpan atribut fase dan tipe kurikulum dengan benar.
     */
    public function test_school_class_fase_and_curriculum_type(): void
    {
        // 1. Simpan kelas baru dengan fase dan tipe kurikulum
        $schoolClass = SchoolClass::create([
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'X-MIPA-1',
            'jenjang' => 'SMA',
            'tingkat' => 10,
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
            'wali_kelas_id' => $this->operatorA->id,
        ]);

        $this->assertDatabaseHas('school_classes', [
            'id' => $schoolClass->id,
            'tenant_id' => $this->tenantA->id,
            'nama_kelas' => 'X-MIPA-1',
            'fase' => 'E',
            'curriculum_type' => 'merdeka',
        ]);

        // 2. Update kelas dengan varian kurikulum Kemenag dan fase F
        $schoolClass->update([
            'nama_kelas' => 'XI-Agama-1',
            'tingkat' => 11,
            'fase' => 'F',
            'curriculum_type' => 'kemenag_merdeka',
        ]);

        $this->assertDatabaseHas('school_classes', [
            'id' => $schoolClass->id,
            'nama_kelas' => 'XI-Agama-1',
            'fase' => 'F',
            'curriculum_type' => 'kemenag_merdeka',
        ]);

        // 3. Verifikasi trait BelongsToTenant pada SchoolClass
        $this->actingAs($this->operatorA);
        $this->assertCount(1, SchoolClass::all());

        $this->actingAs($this->operatorB);
        $this->assertCount(0, SchoolClass::all());
    }
}
