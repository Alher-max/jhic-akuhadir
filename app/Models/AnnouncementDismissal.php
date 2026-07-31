<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnnouncementDismissal extends Model
{
    protected $fillable = ['user_id', 'announcement_id'];
}
