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
        'hpp',
        'tenant_id',
        'balance_amount',
        'gross_revenue', 
        'fee_admin',
        'sales_channel_id'
    ];
    protected $casts = [
        'date' => 'date'
    ];
}
