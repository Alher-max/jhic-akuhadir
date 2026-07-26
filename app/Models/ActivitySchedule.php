<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivitySchedule extends Model
{
    use \App\Traits\BelongsToTenant;
    protected $guarded = [];
}
