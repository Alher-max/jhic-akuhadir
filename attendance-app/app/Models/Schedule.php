<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'day_of_week',
        'specific_date',
        'start_time',
        'end_time',
        'grace_period_minutes',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
