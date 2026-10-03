<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

/**
 * Class InternshipPlacement
 *
 * Model representasi Penempatan Praktik Kerja Lapangan (PKL) Siswa SMK di Industri Mitra (DUDI).
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $class_id
 * @property int $student_id
 * @property int $teacher_supervisor_id
 * @property int|null $industry_location_id
 * @property string $company_name
 * @property string|null $company_address
 * @property string|null $mentor_name
 * @property string|null $mentor_position
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read SchoolClass $schoolClass
 * @property-read User $student
 * @property-read User $teacherSupervisor
 * @property-read Location|null $industryLocation
 * @property-read InternshipAssessment|null $assessment
 * @property-read Tenant $tenant
 */
class InternshipPlacement extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'internship_placements';

    /**
     * Kolom-kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'class_id',
        'student_id',
        'teacher_supervisor_id',
        'industry_location_id',
        'company_name',
        'company_address',
        'mentor_name',
        'mentor_position',
        'start_date',
        'end_date',
    ];

    /**
     * Type casting untuk atribut.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relasi ke Tahun Ajaran aktif penempatan.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Relasi ke Rombel Kelas siswa.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke Siswa yang melaksanakan PKL.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Guru Pembimbing PKL dari sekolah.
     */
    public function teacherSupervisor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_supervisor_id');
    }

    /**
     * Relasi ke Titik Lokasi Geofence HadirYuk di Industri Mitra.
     */
    public function industryLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'industry_location_id');
    }

    /**
     * Relasi ke Hasil Penilaian PKL siswa.
     */
    public function assessment(): HasOne
    {
        return $this->hasOne(InternshipAssessment::class, 'internship_placement_id');
    }
}
