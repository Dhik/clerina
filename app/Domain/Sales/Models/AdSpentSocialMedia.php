<?php

namespace App\Domain\Sales\Models;

use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdSpentSocialMedia extends Model
{
    protected $fillable = [
        'date',
        'amount',
        'social_media_id',
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

    /**
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
