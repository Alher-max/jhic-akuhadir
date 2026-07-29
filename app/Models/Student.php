<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;

class Student extends User
{
    /**
     * Nama tabel yang digunakan oleh Eloquent model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        parent::booted();

        static::addGlobalScope('role_student', function (Builder $builder) {
            $builder->where('role', 'student');
        });

        static::creating(function ($student) {
            $student->role = 'student';
            if (!isset($student->onboarding_completed)) {
                $student->onboarding_completed = true;
            }
            if (is_null($student->email_verified_at)) {
                $student->email_verified_at = now();
            }
        });
    }
}
