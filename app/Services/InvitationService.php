<?php

namespace App\Services;

use App\Models\Invitation;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class InvitationService
{
    /**
     * Invite a new operator for the tenant.
     */
    public function inviteOperator(string $email, ?int $tenantId = null): Invitation
    {
        $tenantId = $tenantId ?? Auth::user()->tenant_id;

        return Invitation::create([
            'tenant_id' => $tenantId,
            'email' => strtolower($email),
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
