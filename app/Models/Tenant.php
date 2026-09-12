<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'institution_type', 'business_category', 'timezone', 'slug', 'code', 'npsn', 'status',
        'subdomain', 'description', 'logo_path', 'banner_path',
        'onboarding_step', 'onboarding_completed', 'attendance_method',
        'attendance_mode', 'session_late_tolerance_minutes',
        'device_token', 'wifi_bssid', 'gps_lat', 'gps_lng', 'gps_radius'
    ];

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function isDailyArrivalMode(): bool
    {
        return ($this->attendance_mode ?? 'daily_arrival') === 'daily_arrival';
    }

    public function isSessionBasedMode(): bool
    {
        return ($this->attendance_mode ?? 'daily_arrival') === 'session_based';
    }

    public function getBannerImageAttribute(): ?string
    {
        return $this->banner_path;
    }
}
