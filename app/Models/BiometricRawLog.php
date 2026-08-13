<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricRawLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'serial_number',
        'raw_payload',
        'status',
        'error_message',
    ];

    protected $casts = [
        'raw_payload' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'serial_number', 'serial_number');
    }
}