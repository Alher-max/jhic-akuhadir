<?php

declare(strict_types=1);

namespace App\Http\Requests\Vocational;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternshipPlacementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'class_id' => ['required', 'integer', 'exists:school_classes,id'],
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'teacher_supervisor_id' => ['required', 'integer', 'exists:users,id'],
            'industry_location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'company_name' => ['required', 'string', 'max:150'],
            'company_address' => ['nullable', 'string'],
            'mentor_name' => ['nullable', 'string', 'max:100'],
            'mentor_position' => ['nullable', 'string', 'max:100'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'class_id.required' => 'Rombel kelas siswa wajib dipilih.',
            'student_id.required' => 'Siswa magang wajib dipilih.',
            'teacher_supervisor_id.required' => 'Guru pembimbing PKL wajib ditentukan.',
            'company_name.required' => 'Nama perusahaan / industri mitra wajib diisi.',
            'start_date.required' => 'Tanggal mulai PKL wajib ditentukan.',
            'end_date.required' => 'Tanggal selesai PKL wajib ditentukan.',
            'end_date.after_or_equal' => 'Tanggal selesai PKL harus sama atau setelah tanggal mulai.',
        ];
    }
}
