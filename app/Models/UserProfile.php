<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_place',
        'birth_date',
        'gender',
        'religion',
        'address',
        'phone_number',
        'employee_id',
        'nuptk',
        'employment_status',
        'rank_group',
        'functional_position',
        'school_name',
        'school_npsn',
        'school_ownership',
        'school_level',
        'sk_appointment',
        'tmt_position',
        'tenure_period',
        'highest_education',
        'university_major',
        'has_educator_certificate',
        'leadership_training_certificate',
        'managerial_experience',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
