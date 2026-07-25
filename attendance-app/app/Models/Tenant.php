<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    protected $fillable = [
        'name', 'institution_type', 'business_category', 'slug', 'code', 'status',
        'subdomain', 'description', 'logo_path', 'banner_path',
        'onboarding_step', 'onboarding_completed', 'attendance_method',
        'attendance_mode', 'session_late_tolerance_minutes',
        'device_token', 'wifi_bssid', 'gps_lat', 'gps_lng', 'gps_radius'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }
}
