<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class InvitationService
{
    /**
     * Invite a new operator for the tenant.
     */
    public function inviteOperator(string $email, ?int $tenantId = null): Invitation
    {
        $tenantId = $tenantId ?? Auth::user()?->tenant_id;
        $email = strtolower(trim($email));

        // 1. Check if email is already registered as an active (non-trashed) User in database (`users` table)
        $existingUser = User::withTrashed()->where('email', $email)->first();
        if ($existingUser && !$existingUser->trashed() && $existingUser->is_active) {
            throw ValidationException::withMessages([
                'email' => 'Email ini sudah terdaftar sebagai pengguna aktif.',
            ]);
        }

        // If the email belongs to a soft-deleted user in another tenant, block to prevent
        // silent tenant changes via invitation. Same-tenant trashed users will be restored
        // during registration (see StaffRegistrationController).
        if ($existingUser && $existingUser->trashed() && $existingUser->tenant_id !== $tenantId) {
            throw ValidationException::withMessages([
                'email' => 'Email ini masih terikat pada instansi lain. Hubungi administrator untuk informasi lebih lanjut.',
            ]);
        }

        // 2. Check if email already has a pending invitation
        $pendingInvitation = Invitation::where('email', $email)
            ->where('status', 'pending')
            ->first();

        if ($pendingInvitation) {
            $pendingInvitation->update([
                'token' => Str::random(40),
                'updated_at' => now(),
            ]);

            throw ValidationException::withMessages([
                'email' => 'Undangan untuk email ini sudah pernah dikirimkan dan masih menunggu registrasi.',
            ]);
        }

        return Invitation::create([
            'tenant_id' => $tenantId,
            'email' => $email,
            'role' => 'operator',
            'token' => Str::random(40),
            'status' => 'pending',
        ]);
    }

    /**
     * Toggle the active status of an operator user.
     */
    public function toggleOperatorStatus(int $operatorId, ?int $tenantId = null): User
    {
        $tenantId = $tenantId ?? Auth::user()->tenant_id;

        $operator = User::where('tenant_id', $tenantId)
            ->whereIn('role', ['operator', 'admin_dapodik'])
            ->findOrFail($operatorId);

        $operator->is_active = !$operator->is_active;
        $operator->save();

        return $operator;
    }
}
