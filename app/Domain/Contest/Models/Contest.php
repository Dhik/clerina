<?php

namespace App\Domain\Contest\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contest extends Model
{
    protected $fillable = [
        'title',
        'budget',
        'used_budget',
        'last_update',
        'total_creator',
        'total_content',
        'cumulative_views',
        'counted_views',
        'interaction',
        'tenant_id'
    ];

    protected $appends = [
        'remaining_budget',
        'remaining_budget_formatted',
        'remaining_percentage'
    ];

    public function getRemainingBudgetAttribute()
    {
        $remaining = $this->budget - $this->used_budget;
        return max($remaining, 0);
    }

    public function getRemainingBudgetFormattedAttribute()
    {
        $remaining = $this->budget - $this->used_budget;
        return 'Rp. ' . number_format(max($remaining, 0), '0', ',', '.');
    }

    public function getRemainingPercentageAttribute()
    {
        $percentage = round(($this->used_budget / $this->budget) * 100);

        return max(100 - $percentage, 0);
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->tenant_id = \Auth::user()->current_tenant_id;
        });
    }

    public function contestContent(): HasMany
    {
        return $this->hasMany(ContestContent::class);
    }
}
