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
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials (Email-Only Architecture).
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $schoolCode = trim($this->input('school_code', ''));
        $email = strtolower(trim($this->input('email') ?? $this->input('login_id') ?? $this->input('login') ?? ''));

        // Langkah 1: Cari Tenant/Sekolah berdasarkan school_code atau npsn
        $tenant = \App\Models\Tenant::where(function ($q) use ($schoolCode) {
            $q->where('code', $schoolCode)
              ->orWhere('code', strtoupper($schoolCode))
              ->orWhere('code', strtolower($schoolCode));
            
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

        // Langkah 2: Email-Only User Lookup (Pure Email Architecture)
        $user = \App\Models\User::where('tenant_id', $tenant->id)
            ->where('email', $email)
            ->first();

        if (! $user || ! \Illuminate\Support\Facades\Hash::check($this->input('password'), $user->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        Auth::login($user, $this->boolean('remember'));
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
            'email' => trans('auth.throttle', [
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
        $email = Str::lower($this->input('email') ?? $this->input('login_id') ?? $this->input('login') ?? '');
        return Str::transliterate($email.'|'.$this->ip());
    }
}
