<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class TeacherService
{
    /**
     * Get all active teachers/educators for a given tenant.
     *
     * @param int|null $tenantId
     * @return Collection
     */
    public function getActiveTeachers(?int $tenantId = null): Collection
    {
        $query = User::activeTeachers();

        if ($tenantId) {
            $query->where('tenant_id', $tenantId);
        }

        return $query->get();
    }
}
