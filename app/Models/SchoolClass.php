<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class SchoolClass extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'jenjang',
        'tingkat',
        'nama_kelas',
        'wali_kelas_id',
    ];

    protected $appends = [
        'full_name',
    ];

    /**
     * Accessor for full_name: prevents double "Kelas" prefix.
     */
    public function getFullNameAttribute(): string
    {
        if (empty($this->nama_kelas)) {
            return 'Tanpa Kelas';
        }

        $nama = trim($this->nama_kelas);
        if (preg_match('/^kelas\b/i', $nama)) {
            return $nama;
        }

        return 'Kelas ' . $nama;
    }

    /**
     * Scope query to order classes logically by level (tingkat) then class name (nama_kelas).
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('tingkat', 'asc')->orderBy('nama_kelas', 'asc');
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function waliKelas()
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function students()
    {
        return $this->hasMany(User::class, 'class_id')->where('role', 'student');
    }
}
