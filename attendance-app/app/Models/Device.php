<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use \App\Traits\BelongsToTenant;
    protected $guarded = [];
}
