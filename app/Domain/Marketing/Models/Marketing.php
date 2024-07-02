<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Marketing extends Model
{
    protected $fillable = [
        'date',
        'type',
        'marketing_category_id',
        'marketing_sub_category_id',
        'amount',
        'tenant_id',
    ];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        // Customize the query to retrieve the model based on your requirements
        return $this->where('id', $value)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();
    }

    /**
     * Casting date
     */
    public function getDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * Relations to Marketing Category
     */
    public function marketingCategory(): BelongsTo
    {
        return $this->belongsTo(MarketingCategory::class);
    }

    /**
     * Relations to Marketing Sub Category
     */
    public function marketingSubCategory(): BelongsTo
    {
        return $this->belongsTo(MarketingSubCategory::class);
    }

    /**
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
