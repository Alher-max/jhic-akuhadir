<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'institution_type', 'business_category', 'slug', 'code', 'status',
        'onboarding_step', 'onboarding_completed', 'attendance_method',
        'device_token', 'wifi_bssid', 'gps_lat', 'gps_lng', 'gps_radius'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
