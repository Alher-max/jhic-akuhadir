<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if (!$this->has('email') || empty($this->email)) {
            $this->merge([
                'email' => $this->user()?->email,
            ]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:10240'],
            // Profile fields
            'birth_place' => ['nullable', 'string', 'max:255'],
            'birth_date' => ['nullable', 'date'],
            'gender' => ['nullable', 'string', 'in:Laki-laki,Perempuan'],
            'religion' => ['nullable', 'string', 'in:Islam,Kristen (Protestan),Katolik,Hindu,Buddha,Khonghucu'],
            'address' => ['nullable', 'string'],
            'phone_number' => ['nullable', 'string', 'max:255'],
            
            'employee_id' => ['nullable', 'string', 'max:255'],
            'nuptk' => ['nullable', 'string', 'max:255'],
            'employment_status' => ['nullable', 'string', 'max:255'],
            'rank_group' => ['nullable', 'string', 'max:255'],
            'functional_position' => ['nullable', 'string', 'max:255'],
            
            'school_name' => ['nullable', 'string', 'max:255'],
            'school_npsn' => ['nullable', 'string', 'max:255'],
            'school_ownership' => ['nullable', 'string', 'max:255'],
            'school_level' => ['nullable', 'string', 'max:255'],
            'sk_appointment' => ['nullable', 'string', 'max:255'],
            'tmt_position' => ['nullable', 'date'],
            'tenure_period' => ['nullable', 'string', 'max:255'],
            
            'highest_education' => ['nullable', 'string', 'max:255'],
            'university_major' => ['nullable', 'string', 'max:255'],
            
            'has_educator_certificate' => ['nullable', 'string', 'max:255'],
            'leadership_training_certificate' => ['nullable', 'string', 'max:255'],
            
            'managerial_experience' => ['nullable', 'string', 'max:255'],
        ];
    }
}
