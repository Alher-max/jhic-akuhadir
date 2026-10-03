<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class AcademicYear
 *
 * Model master data Tahun Ajaran dan Semester untuk modul Rapor Sekolah.
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property string $name Contoh: "2026/2027"
 * @property string $semester "1" untuk Ganjil, "2" untuk Genap
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read string $semester_label Label semester yang mudah dibaca pengguna
 * @property-read string $formatted_period Label gabungan nama dan semester
 * @property-read \App\Models\Tenant $tenant
 * @method static Builder|AcademicYear query()
 * @method static Builder|AcademicYear active()
 * @method static Builder|AcademicYear whereTenantId($value)
 */
class AcademicYear extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'academic_years';

    /**
     * Kolom-kolom yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'name',
        'semester',
        'start_date',
        'end_date',
        'is_active',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data spesifik.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Boot model and register model events.
     */
    protected static function booted(): void
    {
        // Menjaga invarian bahwa dalam 1 tenant hanya ada 1 tahun ajaran aktif
        static::saving(function (self $academicYear) {
            if ($academicYear->is_active) {
                $tenantId = $academicYear->tenant_id ?? auth()->user()?->tenant_id;
                if ($tenantId) {
                    static::withoutGlobalScope('tenant')
                        ->where('tenant_id', $tenantId)
                        ->when($academicYear->exists, function ($query) use ($academicYear) {
                            $query->where('id', '!=', $academicYear->id);
                        })
                        ->update(['is_active' => false]);
                }
            }
        });
    }

    /**
     * Scope untuk mengambil tahun ajaran yang sedang aktif pada tenant.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Mengaktifkan tahun ajaran ini dan menonaktifkan tahun ajaran aktif lainnya pada tenant yang sama.
     *
     * @return bool
     */
    public function activate(): bool
    {
        return DB::transaction(function () {
            static::withoutGlobalScope('tenant')
                ->where('tenant_id', $this->tenant_id)
                ->where('id', '!=', $this->id)
                ->update(['is_active' => false]);

            return $this->update(['is_active' => true]);
        });
    }

    /**
     * Accessor untuk mendapatkan label semester yang informatif.
     *
     * @return string
     */
    public function getSemesterLabelAttribute(): string
    {
        return match ((string) $this->semester) {
            '1' => 'Semester 1 (Ganjil)',
            '2' => 'Semester 2 (Genap)',
            default => 'Semester ' . $this->semester,
        };
    }

    /**
     * Accessor untuk representasi nama tahun ajaran + semester.
     *
     * @return string
     */
    public function getFormattedPeriodAttribute(): string
    {
        return "{$this->name} - {$this->semester_label}";
    }
}
