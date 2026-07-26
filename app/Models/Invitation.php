<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Invitation extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'email',
        'role',
        'token',
        'status',
    ];
}
