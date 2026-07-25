<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Attendance extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'user_id',
        'tenant_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'photo_path',
        'face_match_score',
        'notes',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
