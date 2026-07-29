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
        'attendance_type',
        'class_schedule_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'photo_path',
        'face_match_score',
        'notes',
        'ip_address',
        'is_wifi_verified',
    ];

    protected $casts = [
        'is_wifi_verified' => 'boolean',
        'face_match_score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function classSchedule()
    {
        return $this->belongsTo(\App\Models\ClassSchedule::class, 'class_schedule_id');
    }

    public function isSchoolLevel(): bool
    {
        return ($this->attendance_type ?? 'school') === 'school';
    }

    public function isClassLevel(): bool
    {
        return $this->attendance_type === 'class' || !is_null($this->class_schedule_id);
    }
}
