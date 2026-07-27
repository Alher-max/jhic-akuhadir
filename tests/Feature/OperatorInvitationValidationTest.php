<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\QueryException;
use Tests\TestCase;

class OperatorInvitationValidationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected User $headmaster;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::create([
            'name' => 'Sekolah Contoh',
            'npsn' => '10000001',
            'code' => 'SCH001',
            'slug' => 'sekolah-contoh',
            'subdomain' => 'sekolahcontoh',
        ]);

        $this->headmaster = User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Kepala Sekolah',
            'email' => 'headmaster@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'owner',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    public function test_can_create_operator_invitation_for_new_email(): void
    {
        $response = $this->actingAs($this->headmaster)
            ->post(route('headmaster.super-admin.store'), [
                'email' => 'new.operator@sekolah.sch.id',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Undangan Operator Sekolah berhasil dibuat. Silakan bagikan tautan kepada yang bersangkutan.');

        $this->assertDatabaseHas('invitations', [
            'tenant_id' => $this->tenant->id,
            'email' => 'new.operator@sekolah.sch.id',
            'role' => 'operator',
            'status' => 'pending',
        ]);
    }

    public function test_rejects_invitation_if_email_belongs_to_active_user(): void
    {
        User::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'Guru Aktif',
            'email' => 'active.user@sekolah.sch.id',
            'password' => bcrypt('password'),
            'role' => 'teacher',
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->headmaster)
            ->post(route('headmaster.super-admin.store'), [
                'email' => 'active.user@sekolah.sch.id',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'email' => 'Email ini sudah terdaftar sebagai pengguna aktif.',
        ]);

        $this->assertDatabaseMissing('invitations', [
            'email' => 'active.user@sekolah.sch.id',
        ]);
    }

    public function test_resends_token_and_rejects_duplicate_creation_when_invitation_is_pending(): void
    {
        $existingInvitation = Invitation::create([
            'tenant_id' => $this->tenant->id,
            'email' => 'pending.operator@sekolah.sch.id',
            'role' => 'operator',
            'token' => 'old_token_1234567890',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->headmaster)
            ->post(route('headmaster.super-admin.store'), [
                'email' => 'pending.operator@sekolah.sch.id',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors([
            'email' => 'Undangan untuk email ini sudah pernah dikirimkan dan masih menunggu registrasi.',
        ]);

        // Ensure no duplicate row was created in DB
        $this->assertEquals(1, Invitation::where('email', 'pending.operator@sekolah.sch.id')->count());

        // Ensure token was updated/refreshed
        $updatedInvitation = $existingInvitation->fresh();
        $this->assertNotEquals('old_token_1234567890', $updatedInvitation->token);
    }

    public function test_database_unique_constraint_prevents_simultaneous_duplicates(): void
    {
        Invitation::create([
            'tenant_id' => $this->tenant->id,
            'email' => 'concurrent@sekolah.sch.id',
            'role' => 'operator',
            'token' => 'token_1',
            'status' => 'pending',
        ]);

        $this->expectException(QueryException::class);

        Invitation::create([
            'tenant_id' => $this->tenant->id,
            'email' => 'concurrent@sekolah.sch.id',
            'role' => 'operator',
            'token' => 'token_2',
            'status' => 'pending',
        ]);
    }
}
