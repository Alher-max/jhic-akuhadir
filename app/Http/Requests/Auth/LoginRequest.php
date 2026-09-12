<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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
            'email' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'school_code' => trim((string) $this->input('school_code', '')),
            'email' => Str::lower(trim((string) ($this->input('email') ?? $this->input('login_id') ?? $this->input('login') ?? ''))),
        ]);
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $schoolCode = trim((string) $this->input('school_code', ''));
        $identifier = trim((string) $this->input('email', ''));

        // Langkah 1: Cari Tenant/Sekolah berdasarkan school_code atau npsn
        $tenant = \App\Models\Tenant::where(function ($q) use ($schoolCode) {
            $q->whereRaw('LOWER(TRIM(code)) = ?', [Str::lower($schoolCode)]);
            
            if (\Illuminate\Support\Facades\Schema::hasColumn('tenants', 'npsn')) {
                $q->orWhereRaw('LOWER(TRIM(npsn)) = ?', [Str::lower($schoolCode)]);
            }
        })->first();

        if (! $tenant) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'school_code' => __('Kode Sekolah / NPSN tidak terdaftar.'),
            ]);
        }

        // Siswa dapat memakai email, NISN, atau NIS; akun lain tetap memakai email.
        $user = \App\Models\User::where('tenant_id', $tenant->id)
            ->where(function ($query) use ($identifier) {
                $query->whereRaw('LOWER(TRIM(email)) = ?', [Str::lower($identifier)])
                    ->orWhereRaw('TRIM(nisn) = ?', [$identifier])
                    ->orWhereRaw('TRIM(nis) = ?', [$identifier]);
            })
            ->first();

        $password = (string) $this->input('password');
        $authenticated = $user && Hash::check($password, $user->password);

        if ($user && ! $authenticated && $user->role === 'student' && ! $user->is_password_changed) {
            $defaultPasswords = array_filter([
                $user->nisn,
                $user->nis,
            ]);

            if ($user->birth_date) {
                $birthDate = \Illuminate\Support\Carbon::parse($user->birth_date);
                $defaultPasswords = array_merge($defaultPasswords, [
                    $birthDate->format('dmY'),
                    $birthDate->format('Ymd'),
                    $user->nisn.$birthDate->format('dmY'),
                    $user->nisn.$birthDate->format('Ymd'),
                ]);
            }

            if (in_array($password, array_unique($defaultPasswords), true)) {
                $authenticated = true;

                if ($user->nisn) {
                    $user->password = Hash::make($user->nisn);
                    $user->save();
                }
            }
        }

        if (! $user || ! $authenticated) {
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
