<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CampaignContent extends Model
{
    use FilterByTenant;

    protected $fillable = [
        'campaign_id',
        'key_opinion_leader_id',
        'channel',
        'username',
        'task_name',
        'link',
        'rate_card',
        'product',
        'upload_date',
        'boost_code',
        'is_fyp',
        'is_product_deliver',
        'is_paid',
        'caption',
        'created_by',
        'kode_ads',
        'tenant_id'
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function keyOpinionLeader(): BelongsTo
    {
        return $this->belongsTo(KeyOpinionLeader::class)->withoutGlobalScopes();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function latestStatistic(?Carbon $endDate = null): HasOne
    {
        return $this->hasOne(Statistic::class)->when($endDate !== null, function (Builder $query) use ($endDate) {
            $query->where('date', '<=', $endDate);
        })->latest('date');
    }

}
