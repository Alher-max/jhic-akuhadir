<?php

declare(strict_types=1);

namespace App\Http\Requests\Teacher;

use App\Models\ClassSchedule;
use Illuminate\Foundation\Http\FormRequest;

class BatchStoreSubjectGradeRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak melakukan input nilai pada kelas & mapel ini.
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

        // Untuk guru: pastikan mengajar kombinasi class_id dan subject_id
        $classId = (int) $this->input('class_id');
        $subjectId = (int) $this->input('subject_id');

        return ClassSchedule::where('tenant_id', $user->tenant_id)
            ->where('teacher_id', $user->id)
            ->where('class_id', $classId)
            ->where('subject_id', $subjectId)
            ->exists();
    }

    /**
     * Aturan validasi batch input nilai siswa.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'exists:school_classes,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'grades' => ['required', 'array', 'min:1'],
            'grades.*.student_id' => ['required', 'exists:users,id'],
            'grades.*.score' => ['required', 'numeric', 'min:0', 'max:100'],
            'grades.*.highest_achievement' => ['nullable', 'string', 'max:1000'],
            'grades.*.lowest_achievement' => ['nullable', 'string', 'max:1000'],
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
            'class_id.required' => 'Kelas rombel wajib ditentukan.',
            'class_id.exists' => 'Kelas tidak ditemukan.',
            'subject_id.required' => 'Mata pelajaran wajib ditentukan.',
            'subject_id.exists' => 'Mata pelajaran tidak ditemukan.',
            'grades.required' => 'Daftar nilai siswa tidak boleh kosong.',
            'grades.array' => 'Format data nilai siswa tidak valid.',
            'grades.*.student_id.required' => 'ID Siswa wajib ada.',
            'grades.*.student_id.exists' => 'Data siswa tidak valid.',
            'grades.*.score.required' => 'Nilai akhir siswa wajib diisi.',
            'grades.*.score.numeric' => 'Nilai harus berupa angka numerik.',
            'grades.*.score.min' => 'Nilai siswa tidak boleh kurang dari 0.',
            'grades.*.score.max' => 'Nilai siswa tidak boleh lebih dari 100.',
            'grades.*.highest_achievement.max' => 'Narasi capaian tertinggi maksimal 1000 karakter.',
            'grades.*.lowest_achievement.max' => 'Narasi capaian terendah maksimal 1000 karakter.',
        ];
    }
}
