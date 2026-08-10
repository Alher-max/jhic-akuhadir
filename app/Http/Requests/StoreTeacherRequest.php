<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required_without:teachers_file|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'nisn')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
            'role' => 'nullable|string|in:kepala_sekolah,guru_penggerak,guru_mapel,guru_kelas,guru_kejuruan,guru_bk,guru_inklusi,wali_kelas,staff,pustakawan,laboran,it_support,satpam,caraka,operator,guru,siswa,parent',
            'avatar' => 'nullable|image|max:2048',
            'teachers_file' => 'nullable|file|mimes:csv,txt|max:2048',
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap wajib diisi.',
            'name.required_without' => 'Nama lengkap wajib diisi jika tidak mengunggah file impor guru.',
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari :max karakter.',
            'email.email' => 'Format alamat surel/email tidak valid.',
            'email.max' => 'Alamat surel/email tidak boleh lebih dari :max karakter.',
            'email.unique' => 'Alamat surel/email sudah terdaftar di sistem.',
            'nip.string' => 'NIP/NUPTK harus berupa teks.',
            'nip.max' => 'NIP/NUPTK tidak boleh lebih dari :max karakter.',
            'nip.unique' => 'NIP/NUPTK sudah terdaftar di sistem.',
            'role.string' => 'Peran/Jabatan harus berupa teks.',
            'role.in' => 'Peran/Jabatan yang dipilih tidak valid.',
            'avatar.image' => 'File yang diunggah harus berupa gambar.',
            'avatar.max' => 'Ukuran file gambar tidak boleh lebih dari :max KB.',
            'teachers_file.file' => 'File yang diunggah harus berupa dokumen.',
            'teachers_file.mimes' => 'Format file harus CSV atau TXT.',
            'teachers_file.max' => 'Ukuran file tidak boleh lebih dari :max KB.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Additional duplicate check for manual entry (when not using CSV)
            if ($this->has('name') && $this->name && !$this->hasFile('teachers_file')) {
                $email = $this->email;
                if (!$email) {
                    $identifier = $this->nip ?: \Illuminate\Support\Str::random(6);
                    $email = 'guru.' . $identifier . '@hadirsekolah.id';
                }

                $existing = \App\Models\User::where('email', $email)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->first();

                if ($existing) {
                    $validator->errors()->add('email', 'Data guru dengan email ini sudah ada.');
                }

                if ($this->nip) {
                    $existingNip = \App\Models\User::where('nisn', $this->nip)
                        ->where('tenant_id', auth()->user()->tenant_id)
                        ->first();

                    if ($existingNip) {
                        $validator->errors()->add('nip', 'Data guru dengan NIP/NUPTK ini sudah ada.');
                    }
                }
            }
        });
    }
}