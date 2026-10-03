<?php

declare(strict_types=1);

namespace App\Http\Requests\Homeroom;

use App\Models\StudentReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreExtracurricularGradeRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak menginput nilai ekstrakurikuler.
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

        $isElevated = in_array($user->role, ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'], true);
        if ($isElevated) {
            return true;
        }

        $reportId = (int) $this->input('student_report_id');
        $report = StudentReport::where('tenant_id', $user->tenant_id)->find($reportId);

        if (!$report || !$report->schoolClass) {
            return false;
        }

        return (int) $report->schoolClass->wali_kelas_id === (int) $user->id;
    }

    /**
     * Aturan validasi nilai ekstrakurikuler.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'student_report_id' => ['required', 'exists:student_reports,id'],
            'activity_name' => ['required', 'string', 'max:100'],
            'predicate' => ['required', 'string', 'max:30'],
            'description' => ['nullable', 'string', 'max:1000'],
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
            'student_report_id.required' => 'Lembar rapor siswa wajib disertakan.',
            'student_report_id.exists' => 'Data rapor siswa tidak ditemukan.',
            'activity_name.required' => 'Nama kegiatan ekstrakurikuler wajib diisi.',
            'activity_name.max' => 'Nama kegiatan ekstrakurikuler maksimal 100 karakter.',
            'predicate.required' => 'Predikat penilaian wajib diisi (contoh: Sangat Baik, Baik).',
            'predicate.max' => 'Predikat penilaian maksimal 30 karakter.',
            'description.max' => 'Deskripsi kegiatan ekstrakurikuler maksimal 1000 karakter.',
        ];
    }
}
