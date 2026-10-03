<?php

declare(strict_types=1);

namespace App\Http\Requests\P5;

use App\Models\P5Assessment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchStoreP5AssessmentRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak mengirim request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi penyimpanan massal asesmen P5.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'assessments' => ['nullable', 'array'],
            'assessments.*' => ['array'],
            'assessments.*.*' => ['nullable', 'string', Rule::in(P5Assessment::VALID_PREDICATES)],
            'notes' => ['nullable', 'array'],
            'notes.*' => ['nullable', 'string'],
        ];
    }

    /**
     * Pesan kustom untuk kegagalan validasi predikat.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'assessments.*.*.in' => 'Predikat capaian harus bernilai MB, SB, BSH, atau SAB.',
        ];
    }
}
