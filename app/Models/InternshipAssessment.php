<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class InternshipAssessment
 *
 * Model representasi Lembar Penilaian Praktik Kerja Lapangan (PKL) Siswa SMK.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $internship_placement_id
 * @property float $technical_score Nilai aspek teknis kejuruan (0-100)
 * @property float $softskill_score Nilai budaya kerja & soft skills (0-100)
 * @property float $attendance_score Nilai rekapitulasi kehadiran industri (0-100)
 * @property float $final_score Nilai akhir terbobot (0-100)
 * @property string $predicate "Sangat Baik", "Baik", atau "Cukup"
 * @property string|null $technical_notes Catatan penguasaan kejuruan di industri
 * @property string|null $softskill_notes Catatan etika kerja dan kedisiplinan
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read InternshipPlacement $placement
 * @property-read Tenant $tenant
 */
class InternshipAssessment extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'internship_assessments';

    /**
     * Kolom-kolom yang dapat diisi secara massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'internship_placement_id',
        'technical_score',
        'softskill_score',
        'attendance_score',
        'final_score',
        'predicate',
        'technical_notes',
        'softskill_notes',
    ];

    /**
     * Type casting untuk nilai desimal/angka.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'technical_score' => 'float',
        'softskill_score' => 'float',
        'attendance_score' => 'float',
        'final_score' => 'float',
    ];

    /**
     * Relasi ke Data Penempatan PKL induk.
     */
    public function placement(): BelongsTo
    {
        return $this->belongsTo(InternshipPlacement::class, 'internship_placement_id');
    }
}
