<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTeacherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $teacher = $this->route('teacher');
        return $teacher->tenant_id === auth()->user()->tenant_id
            && in_array($teacher->role, \App\Models\User::getTeacherRoles());
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $teacher = $this->route('teacher');

        return [
            'name' => 'required|string|max:255',
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('users', 'email')
                    ->where(function ($query) {
                        return $query->where('tenant_id', auth()->user()->tenant_id);
                    })
                    ->ignore($teacher->id),
            ],
            'nip' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('users', 'nisn')
                    ->where(function ($query) {
                        return $query->where('tenant_id', auth()->user()->tenant_id);
                    })
                    ->ignore($teacher->id),
            ],
            'role' => 'required|string|in:kepala_sekolah,guru_penggerak,guru_mapel,guru_kelas,guru_kejuruan,guru_bk,guru_inklusi,wali_kelas,staff,pustakawan,laboran,it_support,satpam,caraka,operator,guru,siswa,parent',
            'is_active' => 'required|boolean',
            'class_id' => 'nullable|exists:school_classes,id',
            'avatar' => 'nullable|image|max:2048',
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
            'name.string' => 'Nama lengkap harus berupa teks.',
            'name.max' => 'Nama lengkap tidak boleh lebih dari :max karakter.',
            'email.email' => 'Format alamat surel/email tidak valid.',
            'email.max' => 'Alamat surel/email tidak boleh lebih dari :max karakter.',
            'email.unique' => 'Alamat surel/email sudah terdaftar di sistem.',
            'nip.string' => 'NIP/NUPTK harus berupa teks.',
            'nip.max' => 'NIP/NUPTK tidak boleh lebih dari :max karakter.',
            'nip.unique' => 'NIP/NUPTK sudah terdaftar di sistem.',
            'role.required' => 'Peran / Jabatan sistem wajib dipilih.',
            'role.string' => 'Peran/Jabatan harus berupa teks.',
            'role.in' => 'Peran/Jabatan yang dipilih tidak valid.',
            'is_active.required' => 'Status akun wajib dipilih.',
            'is_active.boolean' => 'Status akun tidak valid.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'avatar.image' => 'File yang diunggah harus berupa gambar.',
            'avatar.max' => 'Ukuran file gambar tidak boleh lebih dari :max KB.',
        ];
    }
}