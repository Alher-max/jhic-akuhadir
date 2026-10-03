<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class P5Assessment
 *
 * Model representasi Penilaian Capaian Siswa per Sub-elemen Target Projek P5/P5RA.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $p5_project_id
 * @property int $p5_project_target_id
 * @property int $student_id
 * @property string $predicate 'MB', 'SB', 'BSH', atau 'SAB'
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read P5Project $project
 * @property-read P5ProjectTarget $target
 * @property-read User $student
 * @property-read Tenant $tenant
 */
class P5Assessment extends Model
{
    use HasFactory, BelongsToTenant;

    public const PREDICATE_MB = 'MB';
    public const PREDICATE_SB = 'SB';
    public const PREDICATE_BSH = 'BSH';
    public const PREDICATE_SAB = 'SAB';

    public const VALID_PREDICATES = [
        self::PREDICATE_MB,
        self::PREDICATE_SB,
        self::PREDICATE_BSH,
        self::PREDICATE_SAB,
    ];

    public const PREDICATE_LABELS = [
        self::PREDICATE_MB => 'Mulai Berkembang',
        self::PREDICATE_SB => 'Sedang Berkembang',
        self::PREDICATE_BSH => 'Berkembang Sesuai Harapan',
        self::PREDICATE_SAB => 'Sangat Berkembang',
    ];

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'p5_assessments';

    /**
     * Kolom-kolom yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'p5_project_id',
        'p5_project_target_id',
        'student_id',
        'predicate',
    ];

    /**
     * Relasi ke Projek P5 induk.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /**
     * Relasi ke Target Dimensi & Sub-elemen yang dinilai.
     */
    public function target(): BelongsTo
    {
        return $this->belongsTo(P5ProjectTarget::class, 'p5_project_target_id');
    }

    /**
     * Relasi ke Siswa yang dinilai.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
