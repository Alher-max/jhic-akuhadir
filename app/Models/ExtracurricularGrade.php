<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class ExtracurricularGrade
 *
 * Model representasi Penilaian Kegiatan Ekstrakurikuler Siswa pada Lembar Rapor.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $student_report_id
 * @property string $activity_name Nama ekstrakurikuler (contoh: "Pramuka", "PMR")
 * @property string $predicate Predikat penilaian (contoh: "Sangat Baik", "Baik")
 * @property string|null $description Keterangan perkembangan siswa di ekstrakurikuler
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read StudentReport $studentReport
 * @property-read Tenant $tenant
 */
class ExtracurricularGrade extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'extracurricular_grades';

    /**
     * Kolom-kolom yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'student_report_id',
        'activity_name',
        'predicate',
        'description',
    ];

    /**
     * Relasi ke Lembar Rapor Siswa.
     */
    public function studentReport(): BelongsTo
    {
        return $this->belongsTo(StudentReport::class, 'student_report_id');
    }
}
