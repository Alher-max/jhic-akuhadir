<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;

class Attendance extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'user_id',
        'tenant_id',
        'location_id',
        'attendance_type',
        'class_schedule_id',
        'date',
        'clock_in',
        'clock_in_time',
        'clock_out',
        'status',
        'photo_path',
        'face_match_score',
        'notes',
        'ip_address',
        'is_wifi_verified',
        'recorded_by_user_id',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'clock_in' => 'datetime',
        'clock_out' => 'datetime',
        'is_wifi_verified' => 'boolean',
        'face_match_score' => 'float',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function location()
    {
        return $this->belongsTo(\App\Models\Location::class, 'location_id');
    }

    public function classSchedule()
    {
        return $this->belongsTo(\App\Models\ClassSchedule::class, 'class_schedule_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'recorded_by_user_id');
    }

    public function isSchoolLevel(): bool
    {
        return ($this->attendance_type ?? 'school') === 'school';
    }

    public function isClassLevel(): bool
    {
        return $this->attendance_type === 'class' || !is_null($this->class_schedule_id);
    }

    protected function clockInTime(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->clock_in ? Carbon::parse($this->clock_in)->format('H:i:s') : null,
            set: fn ($value) => ['clock_in' => $value ? Carbon::parse($value) : null],
        );
    }
}
