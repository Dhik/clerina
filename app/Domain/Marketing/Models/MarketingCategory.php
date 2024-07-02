<?php

namespace App\Domain\Marketing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MarketingCategory extends Model
{
    protected $fillable = [
        'type',
        'name',
    ];

    /**
     * Relation to sub category
     */
    public function marketingSubCategories(): HasMany
    {
        return $this->hasMany(MarketingSubCategory::class);
    }

    /**
     * Relation to Marketing
     */
    public function marketings(): HasMany
    {
        return $this->hasMany(Marketing::class);
    }
}
