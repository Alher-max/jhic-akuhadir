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
