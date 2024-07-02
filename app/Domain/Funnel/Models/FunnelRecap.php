<?php

namespace App\Domain\Funnel\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FunnelRecap extends Model
{
    use FilterByTenant;

    protected $fillable = [
        'date',
        'type',
        'spend',
        'reach',
        'cpr',
        'impression',
        'cpm',
        'frequency',
        'cpv',
        'play_video',
        'link_click',
        'engagement',
        'cpe',
        'cpc',
        'ctr',
        'cplv',
        'cpa',
        'atc',
        'initiated_checkout_number',
        'purchase_number',
        'cost_per_ic',
        'cost_per_atc',
        'cost_per_purchase',
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
}
