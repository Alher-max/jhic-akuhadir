<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'notes',
    ];
}
