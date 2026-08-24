<?php

namespace Tests\Feature;

use App\Models\Invitation;
use App\Models\Tenant;
use App\Models\User;
use App\Services\InvitationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SoftDeletedUserReRegistrationTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tenant = Tenant::factory()->create();
    }

    /**
     * Helper: create a soft-deleted user properly (Eloquent's SoftDeletes
     * prevents setting deleted_at via mass-assign create()).
     */
    protected function createTrashedUser(array $attributes): User
    {
        $user = User::create($attributes);
        // Use Eloquent's delete() so that SoftDeletes properly sets deleted_at timestamp
        $user->delete();
        return $user;
    }

    public function test_soft_deleted_user_can_be_restored_when_accepting_invitation()
    {
        $oldName = 'Almas Herlambang';
        // Create a soft-deleted user that was previously an operator in the same tenant
        $trashedUser = $this->createTrashedUser([
            'tenant_id' => $this->tenant->id,
            'name' => $oldName,
            'email' => 'almas.herlambang7@kesetaraan.belajar.id',
            'password' => bcrypt('old-password'),
            'role' => 'operator',
            'is_active' => false,
            'onboarding_completed' => true,
        ]);

        $this->assertNotNull($trashedUser->deleted_at, 'User should be properly soft-deleted');

        $invitationService = new InvitationService();
        $invitation = $invitationService->inviteOperator($trashedUser->email, $this->tenant->id);
        $this->assertNotNull($invitation);

        // Simulate accepting the invitation
        $payload = [
            'name' => 'Almas Herlambang Updated',
            'password' => 'new-password-123',
            'password_confirmation' => 'new-password-123',
        ];

        $response = $this->post(route('register.staff.store', $invitation->token), $payload);

        // Expect a redirect to OTP verification (successful registration)
        $response->assertRedirect(route('register.verify-otp'));

        // The trashed user should now be restored & updated
        $restoredUser = User::where('email', $trashedUser->email)->first();
        $this->assertNotNull($restoredUser);
        $this->assertNull($restoredUser->deleted_at);
        $this->assertEquals($this->tenant->id, $restoredUser->tenant_id);
        $this->assertEquals('Almas Herlambang Updated', $restoredUser->name);
        $this->assertEquals('operator', $restoredUser->role);
    }

    public function test_invitation_is_blocked_for_active_user_in_another_tenant()
    {
        $otherTenant = Tenant::factory()->create();
        User::create([
            'tenant_id' => $otherTenant->id,
            'name' => 'Someone Else',
            'email' => 'active-user@example.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => true,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new InvitationService())->inviteOperator('active-user@example.com', $this->tenant->id);
    }

    public function test_invitation_is_allowed_for_trashed_user_in_different_tenant_but_restoration_is_same_tenant_only()
    {
        $otherTenant = Tenant::factory()->create();
        // A trashed user in a different tenant - invitation should be blocked during inviter stage.
        $this->createTrashedUser([
            'tenant_id' => $otherTenant->id,
            'name' => 'Old User',
            'email' => 'old-user@example.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => false,
        ]);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        (new InvitationService())->inviteOperator('old-user@example.com', $this->tenant->id);
    }

    public function test_normal_new_user_registration_still_works()
    {
        $invitation = Invitation::create([
            'tenant_id' => $this->tenant->id,
            'email' => 'brand@example.com',
            'role' => 'operator',
            'token' => Str::random(40),
            'status' => 'pending',
        ]);

        $payload = [
            'name' => 'Brand New User',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->post(route('register.staff.store', $invitation->token), $payload);
        $response->assertRedirect(route('register.verify-otp'));

        $user = User::where('email', 'brand@example.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals($invitation->tenant_id, $user->tenant_id);
        $this->assertEquals('operator', $user->role);
        $this->assertNull($user->deleted_at);
    }

    public function test_re_registering_same_tenant_trashed_user_does_not_create_duplicate()
    {
        $trashedUser = $this->createTrashedUser([
            'tenant_id' => $this->tenant->id,
            'name' => 'Deleted Guy',
            'email' => 'deleted@example.com',
            'password' => bcrypt('password'),
            'role' => 'operator',
            'is_active' => false,
            'onboarding_completed' => true,
        ]);

        $invitation = Invitation::create([
            'tenant_id' => $this->tenant->id,
            'email' => 'deleted@example.com',
            'role' => 'operator',
            'token' => Str::random(40),
            'status' => 'pending',
        ]);

        $this->post(route('register.staff.store', $invitation->token), [
            'name' => 'Deleted Guy Restored',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // Only one user record should exist (restored, not duplicated)
        $count = DB::table('users')->where('email', 'deleted@example.com')->count();
        $this->assertEquals(1, $count);

        $this->assertNull(User::where('email', 'deleted@example.com')->first()->deleted_at);
        $this->assertEquals(1, User::where('email', 'deleted@example.com')->count());
    }
}
