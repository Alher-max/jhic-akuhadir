<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'location_id', 'name', 'email', 'avatar', 'password', 'nisn', 'nis', 'nik', 'gender', 'birth_place', 'birth_date', 'religion', 'father_name', 'mother_name', 'parent_phone', 'address', 'blood_type', 'medical_notes', 'pin', 'role', 'onboarding_completed', 'otp_code', 'otp_expires_at', 'parent_id', 'class_id', 'is_active', 'master_photo', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \App\Traits\BelongsToTenant;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function schedules()
    {
        return $this->belongsToMany(Schedule::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function profile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'wali_kelas_id');
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
