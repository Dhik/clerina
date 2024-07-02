<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Offer extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;

    protected $fillable = [
        'campaign_id',
        'status',
        'created_by',
        'financed_by',
        'financed_at',
        'approved_by',
        'approved_at',
        'key_opinion_leader_id',
        'rate_per_slot',
        'benefit',
        'negotiate',
        'acc_slot',
        'rate_total_slot',
        'rate_final_slot',
        'discount',
        'npwp',
        'pph',
        'final_amount',
        'sign_url',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'nik',
        'transfer_status',
        'transfer_date',
        'tenant_id',
        'signed',
        'signed_at'
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by')->withoutGlobalScopes();
    }

    public function financedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financed_by')->withoutGlobalScopes();
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by')->withoutGlobalScopes();
    }

    public function keyOpinionLeader(): BelongsTo
    {
        return $this->belongsTo(KeyOpinionLeader::class);
    }
}
