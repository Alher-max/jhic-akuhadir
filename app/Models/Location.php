<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class Location extends Model
{
    use BelongsToTenant;
    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'latitude',
        'longitude',
        'radius'
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }
}
