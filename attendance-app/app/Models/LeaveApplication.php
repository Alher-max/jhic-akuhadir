<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use \App\Traits\BelongsToTenant;
    protected $guarded = [];
}
