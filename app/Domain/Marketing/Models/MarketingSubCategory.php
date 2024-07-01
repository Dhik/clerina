<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingSubCategory extends Model
{
    protected $fillable = [
        'marketing_category_id',
        'name',
    ];

    /**
     * Relation to marketing category
     */
    public function marketingCategory(): BelongsTo
    {
        return $this->belongsTo(MarketingCategory::class);
    }

    /**
     * Relation to Marketing
     */
    public function marketings(): HasMany
    {
        return $this->hasMany(Marketing::class);
    }
}
