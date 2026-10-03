<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class LearningObjective
 *
 * Model representasi Tujuan Pembelajaran (TP) pada Kurikulum Merdeka.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $subject_id
 * @property int|null $class_id
 * @property string $code Contoh: "TP 1", "TP 2"
 * @property string $description Deskripsi kompetensi tujuan pembelajaran
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read Subject $subject
 * @property-read SchoolClass|null $schoolClass
 * @property-read Tenant $tenant
 */
class LearningObjective extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'learning_objectives';

    /**
     * Kolom-kolom yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'subject_id',
        'class_id',
        'code',
        'description',
    ];

    /**
     * Relasi ke Tahun Ajaran.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Relasi ke Mata Pelajaran.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relasi ke Kelas / Rombel (opsional jika berlaku umum per tingkat/fase).
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Generator kalimat narasi capaian tertinggi berbasis deskripsi TP.
     */
    public function generateHighestNarration(): string
    {
        return 'Menunjukkan penguasaan yang sangat baik dalam ' . lcfirst(trim($this->description));
    }

    /**
     * Generator kalimat narasi capaian terendah berbasis deskripsi TP.
     */
    public function generateLowestNarration(): string
    {
        return 'Perlu bimbingan dan peningkatan dalam ' . lcfirst(trim($this->description));
    }
}
