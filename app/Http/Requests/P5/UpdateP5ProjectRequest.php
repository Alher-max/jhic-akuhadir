<?php

declare(strict_types=1);

namespace App\Http\Requests\P5;

use Illuminate\Foundation\Http\FormRequest;

class UpdateP5ProjectRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak mengirim request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pembaruan Projek P5/P5RA.
     *
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'theme' => ['required', 'string', 'max:100'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string'],
            'coordinator_id' => ['nullable', 'integer', 'exists:users,id'],
            'targets' => ['required', 'array', 'min:1'],
            'targets.*.id' => ['nullable', 'integer'],
            'targets.*.target_type' => ['required', 'string', 'in:pancasila,rahmatan_lil_alamin'],
            'targets.*.dimension' => ['required', 'string', 'max:100'],
            'targets.*.element' => ['nullable', 'string', 'max:150'],
            'targets.*.sub_element' => ['required', 'string', 'max:255'],
            'targets.*.target_description' => ['nullable', 'string'],
        ];
    }
}
