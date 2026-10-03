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
 * Class StudentReport
 *
 * Model representasi Lembar Kompilasi Rapor Siswa per Semester (Kehadiran, Catatan Wali Kelas, Kenaikan Kelas).
 *
 * @package App\Models
 * @property int $id
 * @property int $tenant_id
 * @property int $academic_year_id
 * @property int $class_id
 * @property int $student_id
 * @property int|null $wali_kelas_id
 * @property int $sick_count Jumlah hari sakit
 * @property int $permission_count Jumlah hari izin
 * @property int $alpha_count Jumlah hari tanpa keterangan / alpa
 * @property string|null $homeroom_notes Catatan wali kelas mengenai karakter dan motivasi
 * @property string|null $promotion_status Status kenaikan kelas / kelulusan
 * @property string $status Status rapor: 'draft', 'submitted', 'locked'
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read int $total_absence Total ketidakhadiran
 * @property-read AcademicYear $academicYear
 * @property-read SchoolClass $schoolClass
 * @property-read User $student
 * @property-read User|null $waliKelas
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ExtracurricularGrade> $extracurriculars
 * @property-read Tenant $tenant
 */
class StudentReport extends Model
{
    use HasFactory, BelongsToTenant;

    /**
     * Nama tabel database.
     *
     * @var string
     */
    protected $table = 'student_reports';

    /**
     * Kolom-kolom yang dapat diisi secara mass-assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tenant_id',
        'academic_year_id',
        'class_id',
        'student_id',
        'wali_kelas_id',
        'sick_count',
        'permission_count',
        'alpha_count',
        'homeroom_notes',
        'promotion_status',
        'status',
        'verification_hash',
        'published_at',
    ];

    /**
     * Atribut yang harus di-cast ke tipe data tertentu.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sick_count' => 'integer',
            'permission_count' => 'integer',
            'alpha_count' => 'integer',
            'published_at' => 'datetime',
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
     * Relasi ke Rombel / Kelas.
     */
    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    /**
     * Relasi ke Pengguna Siswa.
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    /**
     * Relasi ke Guru Wali Kelas.
     */
    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    /**
     * Relasi ke Nilai Ekstrakurikuler Siswa.
     */
    public function extracurriculars(): HasMany
    {
        return $this->hasMany(ExtracurricularGrade::class, 'student_report_id');
    }

    /**
     * Accessor untuk total hari tidak hadir (Sakit + Izin + Alpa).
     */
    public function getTotalAbsenceAttribute(): int
    {
        return (int) ($this->sick_count + $this->permission_count + $this->alpha_count);
    }

    /**
     * Menghasilkan hash unik untuk verifikasi publik dokumen rapor.
     */
    public function generateVerificationHash(): string
    {
        if (empty($this->verification_hash)) {
            $this->verification_hash = hash('sha256', (string) $this->id . '-' . (string) $this->tenant_id . '-' . \Illuminate\Support\Str::random(32));
            $this->save();
        }

        return $this->verification_hash;
    }

    /**
     * Accessor untuk URL verifikasi keaslian dokumen rapor.
     */
    public function getVerificationUrlAttribute(): string
    {
        return $this->verification_hash ? route('report.verify', $this->verification_hash) : '';
    }

    /**
     * Memeriksa apakah rapor sudah berstatus rilis/diterbitkan (atau terkunci).
     */
    public function isPublished(): bool
    {
        return in_array($this->status, ['published', 'locked'], true);
    }
}
