<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class VocationalCompetencyAssessment
 *
 * Model representasi Nilai Uji Kompetensi Keahlian (UKK) Siswa SMK.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $class_id
 * @property int $student_id
 * @property string $scheme_name Skema Sertifikasi / Klaster Kejuruan
 * @property string $assessor_name Nama Asesor Penguji
 * @property string $institution_name Lembaga Sertifikasi Profesi (LSP) / DUDI
 * @property float|null $theory_score Nilai Teori Kejuruan (0-100)
 * @property float $practice_score Nilai Praktik Kejuruan (0-100)
 * @property float $final_score Nilai Akhir UKK (0-100)
 * @property string $predicate "Sangat Kompeten", "Kompeten", "Belum Kompeten"
 * @property string|null $certificate_number Nomor Registrasi Sertifikat Kompetensi
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $student
 * @property-read SchoolClass $schoolClass
 * @property-read AcademicYear $academicYear
 * @property-read Tenant $tenant
 */
class VocationalCompetencyAssessment extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'vocational_competency_assessments';

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
        'scheme_name',
        'assessor_name',
        'institution_name',
        'theory_score',
        'practice_score',
        'final_score',
        'predicate',
        'certificate_number',
    ];

    /**
     * Type casting untuk nilai skor.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'theory_score' => 'float',
        'practice_score' => 'float',
        'final_score' => 'float',
    ];

    /**
     * Relasi ke Siswa yang diuji.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Rombel Kelas siswa.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke Tahun Ajaran aktif UKK.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }
}
