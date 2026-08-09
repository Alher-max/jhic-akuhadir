<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['tenant_id', 'location_id', 'name', 'email', 'avatar', 'password', 'nisn', 'nis', 'nik', 'gender', 'birth_place', 'birth_date', 'religion', 'father_name', 'mother_name', 'parent_phone', 'address', 'blood_type', 'medical_notes', 'pin', 'role', 'position', 'onboarding_completed', 'otp_code', 'otp_expires_at', 'parent_id', 'class_id', 'is_active', 'master_photo', 'email_verified_at'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements \Illuminate\Contracts\Auth\MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, \App\Traits\BelongsToTenant;

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::creating(function (User $user) {
            if (in_array($user->role, ['student', 'member']) && is_null($user->email_verified_at)) {
                $user->email_verified_at = now();
            }
        });
    }

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

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
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

    public function teacherProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function studentProfile()
    {
        return $this->hasOne(UserProfile::class);
    }

    public function homeroomClass()
    {
        return $this->hasOne(SchoolClass::class, 'wali_kelas_id');
    }

    public function homeroomClasses()
    {
        return $this->hasMany(SchoolClass::class, 'wali_kelas_id');
    }

    /**
     * Relasi untuk Orang Tua -> Siswa (Anak-anaknya).
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    /**
     * Relasi untuk Siswa -> Orang Tua (Ayah/Ibu/Wali).
     */
    public function parents()
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id')
                    ->withPivot('relationship')
                    ->withTimestamps();
    }

    public static function getTeacherRoles(): array
    {
        return [
            'teacher', 'guru', 'guru_mapel', 'guru_kelas', 'guru_bk', 'guru_inklusi',
            'guru_kejuruan', 'wali_kelas', 'headmaster', 'kepala_sekolah',
            'manager_teacher', 'guru_penggerak', 'operator', 'staff', 'admin_dapodik',
            'pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'
        ];
    }

    /**
     * Determine the system role based on the position/jabatan.
     * This is used for access control and dashboard routing.
     */
    public static function getSystemRoleFromPosition(string $position): string
    {
        return match ($position) {
            'wali_kelas' => 'wali_kelas',
            'headmaster', 'kepala_sekolah' => 'kepala_sekolah',
            'guru_penggerak' => 'guru', // Guru Penggerak uses standard guru role
            default => 'guru', // Standard role for other teachers/staff
        };
    }

    public function scopeActiveTeachers($query)
    {
        return $query->whereIn('role', static::getTeacherRoles())
                     ->where('is_active', true)
                     ->orderBy('name', 'asc');
    }

    /**
     * Dapatkan password default dinamis berdasarkan peran (role) dan identitas pengguna.
     */
    public function getDefaultPassword(): string
    {
        $role = $this->role ?? 'user';

        if (in_array($role, static::getTeacherRoles()) || in_array($role, ['pustakawan', 'laboran', 'it_support', 'satpam', 'caraka'])) {
            return $this->profile?->nuptk
                ?: ($this->profile?->employee_id
                ?: ($this->nisn
                ?: '12345678'));
        }

        if ($role === 'student') {
            return $this->nisn
                ?: ($this->nis
                ?: '12345678');
        }

        if ($role === 'parent') {
            return $this->parent_phone
                ?: ($this->nik
                ?: '12345678');
        }

        return '12345678';
    }

    public static function getDefaultPasswordForUser(User $user): string
    {
        return $user->getDefaultPassword();
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_password_changed' => 'boolean',
            'must_change_password' => 'boolean',
            'is_active' => 'boolean',
            'onboarding_completed' => 'boolean',
        ];
    }

    /**
     * Get the sanitized name for greeting, removing duplicate titles.
     */
    public function getGreetingNameAttribute(): string
    {
        $name = $this->name;
        $titles = ['Bapak', 'Ibu', 'Bpk', 'Bpk.', 'Ibu.', 'Sdr', 'Sdr.', 'Sdri', 'Sdri.'];

        $hasTitle = false;
        foreach ($titles as $title) {
            // Check if name starts with title followed by space or is exactly the title
            if (stripos($name, $title . ' ') === 0 || strcasecmp($name, $title) === 0) {
                $hasTitle = true;
                break;
            }
        }

        return $hasTitle ? $name : "Bapak/Ibu " . $name;
    }
}
