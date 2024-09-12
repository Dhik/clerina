<?php

namespace App\Domain\Tenant\Traits;

use Illuminate\Database\Eloquent\Builder;

trait FilterByTenant
{
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            if (\Auth::check()) {
                $model->tenant_id = \Auth::user()->current_tenant_id;
            }
        });

        self::addGlobalScope(function (Builder $builder) {
            if (\Auth::check()) {
                $builder->where('tenant_id', \Auth::user()->current_tenant_id);
            }
        });
    }
}
