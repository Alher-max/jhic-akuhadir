<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class ActivitySchedule extends Model
{
    use BelongsToTenant;

    protected $guarded = [];

    protected $casts = [
        'target_class_ids' => 'array',
        'is_preset' => 'boolean',
    ];

    public function members()
    {
        return $this->belongsToMany(User::class, 'activity_members', 'activity_schedule_id', 'student_id')
            ->withTimestamps();
    }
}
