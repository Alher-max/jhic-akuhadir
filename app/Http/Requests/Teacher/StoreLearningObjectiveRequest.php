<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use App\Models\ClassSchedule;
use Illuminate\Foundation\Http\FormRequest;

class StoreLearningObjectiveRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan aksi ini.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $allowedRoles = ['teacher', 'guru', 'guru_mapel', 'wali_kelas', 'manager_teacher', 'operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        if (!in_array($user->role, $allowedRoles, true)) {
            return false;
        }

        // Jika operator / headmaster, memiliki hak penuh dalam tenant
        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return true;
        }

        // Untuk guru: pastikan mengajar mata pelajaran tersebut di rombel terkait
        $subjectId = (int) $this->input('subject_id');
        $classId = $this->filled('class_id') ? (int) $this->input('class_id') : null;

        $scheduleQuery = ClassSchedule::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $user->id)
            ->where('subject_id', $subjectId);

        if ($classId) {
            $scheduleQuery->where('class_id', $classId);
        }

        return $scheduleQuery->exists();
    }

    /**
     * Dapatkan aturan validasi untuk formulir TP.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['nullable', 'exists:school_classes,id'],
            'code' => ['required', 'string', 'max:20'],
            'description' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * Pesan kesalahan khusus dalam bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'subject_id.required' => 'Mata pelajaran wajib dipilih.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'class_id.exists' => 'Rombel yang dipilih tidak valid.',
            'code.required' => 'Kode tujuan pembelajaran (contoh: TP 1) wajib diisi.',
            'code.max' => 'Kode tujuan pembelajaran maksimal 20 karakter.',
            'description.required' => 'Deskripsi tujuan pembelajaran wajib diisi.',
            'description.max' => 'Deskripsi tujuan pembelajaran maksimal 1000 karakter.',
        ];
    }
}
