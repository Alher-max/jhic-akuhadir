<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
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
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'school_code' => ['required', 'string'],
            'login_id' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $schoolCode = $this->input('school_code');
        $loginId = $this->input('login_id');

        // Langkah 1: Cari Tenant/Sekolah berdasarkan school_code atau npsn
        $tenant = \App\Models\Tenant::where(function ($q) use ($schoolCode) {
            $q->where('code', $schoolCode)
              ->orWhere('code', strtoupper($schoolCode));
            
            if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                $q->orWhere('npsn', $schoolCode);
            }
        })->first();

        if (! $tenant) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'school_code' => __('Kode Sekolah / NPSN tidak terdaftar.'),
            ]);
        }

        // Langkah 2: Lakukan pencarian User HANYA pada tenant_id sekolah tersebut
        $user = \App\Models\User::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($loginId) {
                $query->where('email', $loginId)
                      ->orWhere('nisn', $loginId);
                
                // Periksa username jika kolomnya ada di database
                if (\Illuminate\Support\Facades\Schema::hasColumn('users', 'username')) {
                    $query->orWhere('username', $loginId);
                }

                // Periksa relasi teacher jika ada
                if (method_exists(\App\Models\User::class, 'teacher')) {
                    $query->orWhereHas('teacher', function ($q) use ($loginId) {
                        $q->where('nip', $loginId)->orWhere('nuptk', $loginId);
                    });
                }

                // Periksa relasi profile
                $query->orWhereHas('profile', function ($q) use ($loginId) {
                    $q->where('employee_id', $loginId) // NIP
                      ->orWhere('nuptk', $loginId);
                });
            })->first();

        if (! $user || ! Auth::attempt(['id' => $user->id, 'password' => $this->input('password')], $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login_id' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login_id' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login_id')).'|'.$this->ip());
    }
}
