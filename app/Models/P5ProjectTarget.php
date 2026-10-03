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
 * Class P5ProjectTarget
 *
 * Model representasi Dimensi & Sub-elemen Target Capaian Projek P5 / P5RA.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $p5_project_id
 * @property string $target_type 'pancasila' atau 'rahmatan_lil_alamin'
 * @property string $dimension
 * @property string|null $element
 * @property string $sub_element
 * @property string|null $target_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read P5Project $project
 * @property-read \Illuminate\Database\Eloquent\Collection<int, P5Assessment> $assessments
 * @property-read Tenant $tenant
 */
class P5ProjectTarget extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'p5_project_targets';

    /**
     * Kolom-kolom yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'p5_project_id',
        'target_type',
        'dimension',
        'element',
        'sub_element',
        'target_description',
    ];

    /**
     * Relasi ke Projek P5 induk.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /**
     * Relasi ke Penilaian Siswa untuk target ini.
     */
    public function assessments(): HasMany
    {
        return $this->hasMany(P5Assessment::class, 'p5_project_target_id');
    }
}
