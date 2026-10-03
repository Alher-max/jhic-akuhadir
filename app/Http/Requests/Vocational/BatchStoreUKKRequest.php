<?php

declare(strict_types=1);

namespace App\Http\Requests\Vocational;

use Illuminate\Foundation\Http\FormRequest;

class BatchStoreUKKRequest extends FormRequest
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
            'assessments' => ['required', 'array'],
            'assessments.*.scheme_name' => ['required', 'string', 'max:150'],
            'assessments.*.assessor_name' => ['required', 'string', 'max:100'],
            'assessments.*.institution_name' => ['required', 'string', 'max:150'],
            'assessments.*.theory_score' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'assessments.*.practice_score' => ['required', 'numeric', 'min:0', 'max:100'],
            'assessments.*.certificate_number' => ['nullable', 'string', 'max:100'],
        ];
    }
}
