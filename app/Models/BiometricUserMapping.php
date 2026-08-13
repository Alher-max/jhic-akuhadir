<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricUserMapping extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'biometric_pin',
        'student_id',
        'teacher_id',
    ];

    public function school(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'school_id');
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function getUser(): ?User
    {
        return $this->student ?? $this->teacher;
    }

    public function getUserType(): ?string
    {
        if ($this->student_id) {
            return 'student';
        }
        if ($this->teacher_id) {
            return 'teacher';
        }
        return null;
    }
}