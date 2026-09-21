<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant()
    {
        static::creating(function ($model) {
            if (! $model->tenant_id && auth()->hasUser()) {
                $model->tenant_id = auth()->user()->tenant_id;
            }
        });

        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->hasUser() && auth()->user()->tenant_id) {
                $from = $builder->getQuery()->from;
                if (stripos($from, ' as ') !== false) {
                    $segments = preg_split('/\s+as\s+/i', $from);
                    $tableName = trim(end($segments));
                } elseif (strpos($from, ' ') !== false) {
                    $segments = explode(' ', $from);
                    $tableName = trim(end($segments));
                } else {
                    $tableName = $from;
                }
                $builder->where($tableName . '.tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function tenant()
    {
        return $this->belongsTo(\App\Models\Tenant::class);
    }
}
