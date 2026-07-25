<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class AttendanceSetting extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'method_rfid',
        'method_qrcode',
        'method_biometric',
        'method_pwa',
        'method_manual',
        'method_wifi',
        'wifi_allowed_ssids',
        'wifi_allowed_macs',
        'rfid_secret_key',
        'latitude',
        'longitude',
        'radius_meters',
        'biometric_ip_address',
        'is_liveness_active',
    ];

    protected $casts = [
        'method_rfid' => 'boolean',
        'method_qrcode' => 'boolean',
        'method_biometric' => 'boolean',
        'method_manual' => 'boolean',
        'method_wifi' => 'boolean',
        'is_liveness_active' => 'boolean',
        'wifi_allowed_ssids' => 'array',
        'wifi_allowed_macs' => 'array',
    ];
}
