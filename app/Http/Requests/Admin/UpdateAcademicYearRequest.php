<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAcademicYearRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna memiliki otorisasi untuk membuat permintaan ini.
     */
    public function authorize(): bool
    {
        $user = $this->user();
        if (!$user) {
            return false;
        }

        $allowedRoles = ['operator', 'headmaster', 'admin', 'admin_dapodik', 'kepala_sekolah', 'owner'];
        return in_array($user->role, $allowedRoles, true);
    }

    /**
     * Persiapkan data sebelum validasi dijalankan.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge([
                'is_active' => $this->boolean('is_active'),
            ]);
        }
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:50'],
            'semester' => ['required', 'string', 'in:1,2'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Pesan kesalahan khusus untuk validasi.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama tahun ajaran wajib diisi.',
            'name.string' => 'Nama tahun ajaran harus berupa teks.',
            'name.max' => 'Nama tahun ajaran maksimal 50 karakter.',
            'semester.required' => 'Semester wajib dipilih.',
            'semester.in' => 'Semester harus bernilai 1 (Ganjil) atau 2 (Genap).',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.date' => 'Format tanggal mulai tidak valid.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.date' => 'Format tanggal selesai tidak valid.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama dengan atau setelah tanggal mulai.',
            'is_active.boolean' => 'Status aktif harus bernilai benar atau salah.',
        ];
    }
}
