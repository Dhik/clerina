<?php

namespace App\Domain\Marketing\Models;

use App\Domain\Funnel\Models\Funnel;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SocialMedia extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Relations to AdSpentSocialMedia
     */
    public function adSpentSocialMedia(): HasMany
    {
        return $this->hasMany(AdSpentSocialMedia::class);
    }

    /**
     * Relations to Funnel
     */
    public function funnels(): HasMany
    {
        return $this->hasMany(Funnel::class);
    }
}
