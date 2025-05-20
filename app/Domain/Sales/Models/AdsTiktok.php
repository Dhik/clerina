<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdsTiktok extends Model
{
    protected $table = 'ads_tiktok';

    protected $fillable = [
        'date',
        'amount_spent',
        'impressions',
        'content_views_shared_items',
        'adds_to_cart_shared_items',
        'purchases_shared_items',
        'purchases_conversion_value_shared_items',
        'tenant_id',
        'link_clicks',
        'kategori_produk',
        'campaign_name',
        'account_name',
        'pic',
        'last_updated',
        'new_created',
        'primary_status',
        'campaign_budget',
        'product_page_views',
        'items_purchased',
        'cpm',
        'cpc',
        'cost_per_purchase',
        'ctr',
        'purchase_rate',
        'average_order_value',
        'live_views',
        'biaya_bersih',
        'roi',
        'mata_uang',
        'type',
    ];

    protected $casts = [
        'date' => 'date',
        'amount_spent' => 'integer',
        'impressions' => 'integer',
        'content_views_shared_items' => 'float',
        'adds_to_cart_shared_items' => 'float',
        'purchases_shared_items' => 'float',
        'purchases_conversion_value_shared_items' => 'float',
        'link_clicks' => 'float',
        'campaign_budget' => 'float',
        'product_page_views' => 'integer',
        'items_purchased' => 'integer',
        'cpm' => 'float',
        'cpc' => 'float',
        'cost_per_purchase' => 'float',
        'ctr' => 'float',
        'purchase_rate' => 'float',
        'average_order_value' => 'float',
        'live_views' => 'integer',
        'biaya_bersih' => 'decimal:2',
        'roi' => 'decimal:2',
    ];
}
