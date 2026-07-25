<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStudentRequest extends FormRequest
{
    /**
     * Tentukan apakah pengguna berhak membuat permintaan ini.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Dapatkan aturan validasi yang berlaku untuk permintaan ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nisn' => ['required', 'string', 'max:50', 'unique:users,nisn'],
            'nis' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'gender' => ['nullable', 'in:L,P'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'class_id' => ['required', 'exists:school_classes,id'],
            'parent_option' => ['required', 'in:new,existing'],
            'parent_name' => ['required_if:parent_option,new', 'nullable', 'string', 'max:255'],
            'father_name' => ['nullable', 'string', 'max:255'],
            'mother_name' => ['nullable', 'string', 'max:255'],
            'parent_phone' => ['nullable', 'string', 'max:50'],
            'parent_email' => ['nullable', 'string', 'max:255'],
            'parent_id' => ['required_if:parent_option,existing', 'nullable', 'exists:users,id'],
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'religion' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'max:10'],
            'medical_notes' => ['nullable', 'string'],
            'master_photo' => ['nullable', 'image', 'max:2048'],
        ];
    }

    /**
     * Pesan kesalahan khusus.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap siswa wajib diisi.',
            'nisn.required' => 'NISN siswa wajib diisi.',
            'nisn.unique' => 'NISN sudah terdaftar dalam sistem.',
            'gender.in' => 'Pilihan jenis kelamin harus Laki-laki (L) atau Perempuan (P).',
            'birth_date.date' => 'Format tanggal lahir tidak valid.',
            'email.email' => 'Format alamat surel tidak valid.',
            'email.unique' => 'Alamat surel sudah terdaftar.',
            'class_id.required' => 'Kelas / Rombel wajib dipilih.',
            'class_id.exists' => 'Kelas yang dipilih tidak valid.',
            'parent_name.required_if' => 'Nama Orang Tua / Wali wajib diisi jika membuat akun baru.',
            'parent_id.required_if' => 'Orang Tua / Wali terdaftar wajib dipilih.',
            'master_photo.image' => 'File foto master harus berupa gambar (JPG, PNG, WebP).',
            'master_photo.max' => 'Ukuran foto master maksimal 2MB.',
        ];
    }
}
