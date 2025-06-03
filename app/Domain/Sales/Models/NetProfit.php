<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetProfit extends Model
{
    use FilterByTenant;

    protected $table = 'net_profits';
    
    protected $fillable = [
        'date',
        'sales',
        'hpp',
        'tenant_id',
        'balance_amount',
        'gross_revenue', 
        'fee_admin',
        'sales_channel_id',
        'b2b_sales',
        'crm_sales',
        'marketing',
        'spent_kol',
        'affiliate',
        'operasional',
        'fee_packing',
        'roas',
        'romi',
        'visit',
        'qty',
        'order',
        'closing_rate'
    ];
    
    protected $casts = [
        'date' => 'date',
        'sales' => 'decimal:2',
        'b2b_sales' => 'decimal:2',
        'crm_sales' => 'decimal:2',
        'marketing' => 'decimal:2',
        'spent_kol' => 'decimal:2',
        'affiliate' => 'decimal:2',
        'operasional' => 'decimal:2',
        'hpp' => 'decimal:2',
        'fee_packing' => 'decimal:2',
        'roas' => 'decimal:2',
        'romi' => 'decimal:2',
        'closing_rate' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'gross_revenue' => 'decimal:2',
        'fee_admin' => 'decimal:2'
    ];
}