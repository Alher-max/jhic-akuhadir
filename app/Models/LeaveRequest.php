<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class LeaveRequest extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'user_id',
        'tenant_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
