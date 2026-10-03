<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * Class P5Project
 *
 * Model representasi Data Master Projek Penguatan Profil Pelajar Pancasila (P5) & P5RA.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $class_id
 * @property int $coordinator_id
 * @property string $theme
 * @property string $title
 * @property string $description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read SchoolClass $schoolClass
 * @property-read User $coordinator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, P5ProjectTarget> $targets
 * @property-read \Illuminate\Database\Eloquent\Collection<int, P5Assessment> $assessments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, P5StudentNote> $studentNotes
 * @property-read Tenant $tenant
 */
class P5Project extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'p5_projects';

    /**
     * Kolom-kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'class_id',
        'coordinator_id',
        'theme',
        'title',
        'description',
    ];

    /**
     * Relasi ke Tahun Ajaran aktif.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Relasi ke Rombel Kelas sasaran projek.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke Guru Koordinator / Fasilitator projek.
     */
    public function coordinator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coordinator_id');
    }

    /**
     * Relasi ke Target Dimensi & Sub-elemen projek.
     */
    public function targets(): HasMany
    {
        return $this->hasMany(P5ProjectTarget::class, 'p5_project_id');
    }

    /**
     * Relasi ke seluruh Penilaian Siswa dalam projek.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_id');
    }

    /**
     * Relasi ke Catatan Proses Siswa dalam projek.
     */
    public function studentNotes(): HasMany
    {
        return $this->hasMany(P5StudentNote::class, 'p5_project_id');
    }
}
