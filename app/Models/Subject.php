<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Subject extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'code',
        'name',
        'is_preset',
    ];

    protected $casts = [
        'is_preset' => 'boolean',
    ];

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = $value ? strtoupper(trim($value)) : null;
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function schedules()
    {
        return $this->hasMany(ClassSchedule::class);
    }
}
