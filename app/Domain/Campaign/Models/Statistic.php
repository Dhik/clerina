<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Statistic extends Model
{
    protected $fillable = [
        'date',
        'campaign_id',
        'campaign_content_id',
        'view',
        'like',
        'comment',
        'tenant_id',
        'cpm',
        'engagement'
    ];

    protected $appends = [
        'positive_like',
    ];

    public static function boot()
    {
        parent::boot();

        self::saving(function ($model) {
            $model->engagement = $model->view + $model->like + $model->comment;
        });
    }

    /**
     * Return positive_like
     */
    public function getPositiveLikeAttribute(): string
    {

        if (!empty($this->like)) {
            return $this->like < 0 ? abs($this->like) : $this->like;
        }

        return 0;
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function campaignContent(): BelongsTo
    {
        return $this->belongsTo(CampaignContent::class);
    }

    protected function calculateCPM($view, $rate) {
        if ($view === 0) {
            return 0;
        }

        return ($rate / $view) * 1000;
    }
}
