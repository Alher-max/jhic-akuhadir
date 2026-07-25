<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'device_name',
        'device_type',
        'location',
        'ip_address',
        'status',
    ];
}
