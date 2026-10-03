<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class P5StudentNote
 *
 * Model representasi Catatan Perkembangan Proses Siswa dari Fasilitator Projek P5/P5RA.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $p5_project_id
 * @property int $student_id
 * @property string|null $process_notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read P5Project $project
 * @property-read User $student
 * @property-read Tenant $tenant
 */
class P5StudentNote extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'p5_student_notes';

    /**
     * Kolom-kolom yang dapat diisi massal.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'p5_project_id',
        'student_id',
        'process_notes',
    ];

    /**
     * Relasi ke Projek P5 induk.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(P5Project::class, 'p5_project_id');
    }

    /**
     * Relasi ke Siswa pemilik catatan proses.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }
}
