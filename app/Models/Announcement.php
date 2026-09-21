<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_class_id',
        'teacher_id',
        'title',
        'description',
        'attachment_path',
        'target_audience',
        'expired_at',
    ];

    protected $casts = [
        'school_class_id' => 'integer',
        'teacher_id' => 'integer',
        'target_audience' => 'string',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class);
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function dismissals()
    {
        return $this->hasMany(AnnouncementDismissal::class);
    }

    public function scopeForTenant($query, $tenantId)
    {
        return $query->whereHas('schoolClass', function ($q) use ($tenantId) {
            $q->where('tenant_id', $tenantId);
        });
    }
}
