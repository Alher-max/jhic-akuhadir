<?php

declare(strict_types=1);

namespace App\Http\Requests\Homeroom;

use App\Models\SchoolClass;
use Illuminate\Foundation\Http\FormRequest;

class BatchUpdateStudentReportRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan update lembar rapor kelas ini.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $allowedRoles = ['wali_kelas', 'teacher', 'guru', 'guru_mapel', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        if (!in_array($user->role, $allowedRoles, true)) {
            return false;
        }

        // Operator & Kepsek memiliki hak supervisi seluruh kelas di tenant
        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return true;
        }

        // Untuk wali kelas: pastikan benar mengampu kelas bersangkutan
        $class = $this->route('class');
        $classId = (int) ($this->input('class_id') ?: (is_object($class) ? $class->id : $class));
        $schoolClass = SchoolClass::where('tenant_id', $user->tenant_id)->find($classId);

        return $schoolClass && (int) $schoolClass->wali_kelas_id === (int) $user->id;
    }

    /**
     * Persiapkan input sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('class_id') && $this->route('class')) {
            $class = $this->route('class');
            $this->merge([
                'class_id' => is_object($class) ? $class->id : $class,
            ]);
        }
    }

    /**
     * Aturan validasi pembaruan lembar rapor secara massal.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:school_classes,id'],
            'reports' => ['required', 'array', 'min:1'],
            'reports.*.student_id' => ['required', 'exists:users,id'],
            'reports.*.sick_count' => ['nullable', 'integer', 'min:0'],
            'reports.*.permission_count' => ['nullable', 'integer', 'min:0'],
            'reports.*.alpha_count' => ['nullable', 'integer', 'min:0'],
            'reports.*.homeroom_notes' => ['nullable', 'string', 'max:2000'],
            'reports.*.promotion_status' => ['nullable', 'string', 'max:50'],
            'reports.*.status' => ['nullable', 'string', 'in:draft,submitted,locked'],
        ];
    }

    /**
     * Pesan kesalahan kustom berbahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Kelas wajib ditentukan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'reports.required' => 'Data rekapitulasi rapor tidak boleh kosong.',
            'reports.*.student_id.required' => 'ID Siswa wajib ada.',
            'reports.*.sick_count.integer' => 'Jumlah hari sakit harus berupa angka bulat.',
            'reports.*.sick_count.min' => 'Jumlah hari sakit tidak boleh negatif.',
            'reports.*.permission_count.integer' => 'Jumlah hari izin harus berupa angka bulat.',
            'reports.*.permission_count.min' => 'Jumlah hari izin tidak boleh negatif.',
            'reports.*.alpha_count.integer' => 'Jumlah hari alpa harus berupa angka bulat.',
            'reports.*.alpha_count.min' => 'Jumlah hari alpa tidak boleh negatif.',
            'reports.*.homeroom_notes.max' => 'Catatan wali kelas maksimal 2000 karakter.',
        ];
    }
}
