<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class SubjectGrade
 *
 * Model representasi Nilai Akhir & Narasi Capaian Pembelajaran per Siswa.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $class_id
 * @property int $subject_id
 * @property int $student_id
 * @property int $teacher_id
 * @property float $score Nilai akhir mata pelajaran (0 - 100)
 * @property string|null $highest_achievement Narasi capaian tertinggi yang dikuasai
 * @property string|null $lowest_achievement Narasi capaian yang perlu pendampingan/peningkatan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read AcademicYear $academicYear
 * @property-read SchoolClass $schoolClass
 * @property-read Subject $subject
 * @property-read User $student
 * @property-read User $teacher
 * @property-read Tenant $tenant
 */
class SubjectGrade extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'subject_grades';

    /**
     * Kolom-kolom yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'class_id',
        'subject_id',
        'student_id',
        'teacher_id',
        'score',
        'highest_achievement',
        'lowest_achievement',
    ];

    /**
     * Atribut yang di-cast ke tipe data spesifik.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'score' => 'float',
        ];
    }

    /**
     * Relasi ke Tahun Ajaran.
     */
    public function academicYear(): BelongsTo
    {
        return $this->belongsTo(AcademicYear::class, 'academic_year_id');
    }

    /**
     * Relasi ke Kelas / Rombel.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke Mata Pelajaran.
     */
    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class, 'subject_id');
    }

    /**
     * Relasi ke Pengguna Siswa.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Guru Pengampu.
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }
}
