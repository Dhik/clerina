<?php

namespace App\Domain\AffiliateTalent\Models;

use Illuminate\Database\Eloquent\Model;

class AffiliateTalent extends Model
{
    protected $table = 'affiliate_talents';
    protected $fillable = [
        'username',
        'pic',
        'gmv_bottom',
        'gmv_top',
        'contact_ig',
        'contact_wa_notelp',
        'contact_tiktok',
        'contact_email',
        'platform_menghubungi',
        'status_call',
        'rate_card',
        'rate_card_final',
        'roas',
        'keterangan',
        'sales_channel_id',
        'tenant_id'
    ];
    public function salesChannel()
    {
        return $this->belongsTo(SalesChannel::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}