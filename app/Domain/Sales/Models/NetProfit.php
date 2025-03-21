<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;

class NetProfit extends Model
{
    use FilterByTenant, InteractsWithMedia;

    protected $table = 'net_profits';
    protected $fillable = [
        'date',
        'sales',
        'marketing',
        'spent_kol',
        'affiliate',
        'operasional',
        'hpp',
        'roas',
        'visit',
        'qty',
        'order',
        'closing_rate',
        'tenant_id',
        'crm_sales',
        'b2b_sales',
    ];
    protected $casts = [
        'date' => 'date'
    ];
}
