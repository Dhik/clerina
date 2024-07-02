<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketingRecap extends Model
{
    protected $fillable = [
        'date',
        'total_marketing',
        'total_branding',
        'tenant_id',
    ];

    /**
     * Casting date
     */
    public function getDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
