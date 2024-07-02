<?php

namespace App\Domain\Funnel\Models;

use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Funnel extends Model
{
    use FilterByTenant;

    protected $fillable = [
        'date',
        'type',
        'social_media_id',
        'spend',
        'reach',
        'cpr',
        'impression',
        'cpm',
        'frequency',
        'cpv',
        'play_video',
        'link_click',
        'cpc',
        'engagement',
        'cpe',
        'ctr',
        'cplv',
        'cpa',
        'atc',
        'initiated_checkout_number',
        'purchase_number',
        'cost_per_ic',
        'cost_per_purchase',
        'cost_per_atc',
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
     * Relations to social media
     */
    public function socialMedia(): BelongsTo
    {
        return $this->belongsTo(SocialMedia::class);
    }
}
