<?php

namespace Tests\Feature;

use App\Models\Subject;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SubjectCodeValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $operator;
    protected Subject $subject1;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'SMA Negeri 1 Jakarta',
            'code' => '20102026',
            'slug' => 'sman1jkt',
            'onboarding_completed' => true,
        ]);

        $this->operator = User::factory()->create([
            'tenant_id' => $this->tenant->id,
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->subject1 = Subject::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Matematika',
            'code' => 'MTK',
        ]);
    }

    /**
     * Test: Kode mapel otomatis diubah menjadi huruf kapital (uppercase).
     */
    public function test_subject_code_is_automatically_converted_to_uppercase(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('subjects.store'), [
                'name' => 'Bahasa Indonesia',
                'code' => 'bin-01',
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'subjects']));
        $this->assertDatabaseHas('subjects', [
            'tenant_id' => $this->tenant->id,
            'name' => 'Bahasa Indonesia',
            'code' => 'BIN-01',
        ]);
    }

    /**
     * Test: Gagal membuat mapel dengan kode duplikat di sekolah/tenant yang sama.
     */
    public function test_cannot_create_subject_with_duplicate_code_in_same_tenant(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('subjects.store'), [
                'name' => 'Matematika Lanjut',
                'code' => 'mtk', // Duplikat MTK
            ]);

        $response->assertSessionHasErrors(['code']);
        $errors = session('errors')->get('code');
        $this->assertContains('Kode mapel ini sudah digunakan.', $errors);
    }

    /**
     * Test: Gagal membuat mapel jika kode melebihi 10 karakter.
     */
    public function test_cannot_create_subject_with_code_exceeding_10_characters(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('subjects.store'), [
                'name' => 'Fisika Kuantum',
                'code' => 'FISIKAKUANTUM123', // 16 karakter
            ]);

        $response->assertSessionHasErrors(['code']);
        $errors = session('errors')->get('code');
        $this->assertContains('Kode mapel maksimal 10 karakter.', $errors);
    }

    /**
     * Test: Gagal membuat mapel jika terdapat karakter ilegal atau spasi.
     */
    public function test_cannot_create_subject_with_illegal_characters_or_spaces(): void
    {
        $response = $this->actingAs($this->operator)
            ->post(route('subjects.store'), [
                'name' => 'Kimia Organik',
                'code' => 'KIM IA#1', // Spasi & tanda pagar
            ]);

        $response->assertSessionHasErrors(['code']);
        $errors = session('errors')->get('code');
        $this->assertContains('Kode mapel hanya boleh berisi huruf, angka, dan tanda hubung tanpa spasi.', $errors);
    }

    /**
     * Test: Berhasil meng-update mapel dengan mempertahankan kode yang sama.
     */
    public function test_can_update_subject_keeping_same_code(): void
    {
        $response = $this->actingAs($this->operator)
            ->put(route('subjects.update', $this->subject1->id), [
                'name' => 'Matematika Wajib',
                'code' => 'mtk',
            ]);

        $response->assertRedirect(route('class-schedules.index', ['tab' => 'subjects']));
        $this->assertDatabaseHas('subjects', [
            'id' => $this->subject1->id,
            'name' => 'Matematika Wajib',
            'code' => 'MTK',
        ]);
    }

    /**
     * Test: Gagal meng-update mapel ke kode yang sudah dipakai mapel lain di tenant yang sama.
     */
    public function test_cannot_update_subject_to_code_already_used_by_another_subject(): void
    {
        $subject2 = Subject::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Bahasa Inggris',
            'code' => 'BIG',
        ]);

        $response = $this->actingAs($this->operator)
            ->put(route('subjects.update', $subject2->id), [
                'name' => 'Bahasa Inggris',
                'code' => 'MTK', // Bentrok dengan subject1
            ]);

        $response->assertSessionHasErrors(['code']);
        $errors = session('errors')->get('code');
        $this->assertContains('Kode mapel ini sudah digunakan.', $errors);
    }
}
