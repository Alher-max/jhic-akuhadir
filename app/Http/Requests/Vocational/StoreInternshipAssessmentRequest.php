<?php

declare(strict_types=1);

namespace App\Http\Requests\Vocational;

use Illuminate\Foundation\Http\FormRequest;

class StoreInternshipAssessmentRequest extends FormRequest
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
            'technical_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'softskill_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'attendance_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'technical_notes' => ['nullable', 'string'],
            'softskill_notes' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'technical_score.required' => 'Nilai kompetensi teknis wajib diisi.',
            'technical_score.min' => 'Nilai kompetensi teknis minimal 0.',
            'technical_score.max' => 'Nilai kompetensi teknis maksimal 100.',
            'softskill_score.required' => 'Nilai budaya kerja & softskill wajib diisi.',
            'attendance_score.required' => 'Nilai kehadiran wajib diisi.',
        ];
    }
}
