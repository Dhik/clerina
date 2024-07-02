<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesByChannel extends Model
{
    protected $fillable = [
        'date',
        'sales_channel_id',
        'visit',
        'qty',
        'order',
        'closing_rate',
        'turnover',
        'ad_spent_total',
        'roas',
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
     * Relations to sales channel
     */
    public function salesChannel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class);
    }

    /**
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
