<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class SchoolClass
 *
 * Model representasi rombel (rombongan belajar) di sekolah.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property string $jenjang SD, SMP, SMA, SMK
 * @property int $tingkat Angka tingkat kelas (misal: 10)
 * @property string $nama_kelas Nama kelas (misal: "X-A")
 * @property string|null $fase Fase kurikulum merdeka (A, B, C, D, E, F)
 * @property string $curriculum_type Tipe kurikulum: 'merdeka', 'k13', 'kemenag_merdeka'
 * @property int|null $wali_kelas_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $waliKelas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, User> $students
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Announcement> $announcements
 * @property-read Tenant $tenant
 * @property-read string $full_name
 */
class SchoolClass extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_kelas',
        'jenjang',
        'tingkat',
        'fase',
        'curriculum_type',
        'tenant_id',
        'wali_kelas_id',
    ];

    /**
     * Relasi ke pengumuman kelas.
     */
    public function announcements(): HasMany
    {
        return $this->hasMany(Announcement::class);
    }
    
    /**
     * Relasi ke guru wali kelas.
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relasi ke siswa dalam rombel ini.
     */
    public function students(): HasMany
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }

    /**
     * Scope eksplisit untuk backward compatibility.
     */
    public function scopeForTenant($query, $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Accessor untuk nama lengkap kelas.
     */
    public function getFullNameAttribute(): string
    {
        return $this->nama_kelas;
    }
}