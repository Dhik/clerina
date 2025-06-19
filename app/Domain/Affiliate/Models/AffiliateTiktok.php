<?php

namespace App\Domain\Affiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AffiliateTiktok extends Model
{
    use HasFactory;

    protected $table = 'affiliate_tiktok';
    
    protected $fillable = [
        'creator_username',
        'affiliate_gmv',
        'affiliate_live_gmv',
        'affiliate_shoppable_video',
        'affiliate_product_card_gmv',
        'affiliate_products_sold',
        'items_sold',
        'est_commission',
        'avg_order_value',
        'affiliate_orders',
        'ctr',
        'product_impressions',
        'avg_affiliate_customers',
        'affiliate_live_streams',
        'open_collaboration_gmv',
        'open_collaboration_est',
        'affiliate_refunded_gmv',
        'affiliate_items_refunded',
        'affiliate_followers',
        'date',
    ];

    protected $casts = [
        'date' => 'date',
        'affiliate_gmv' => 'integer',
        'affiliate_live_gmv' => 'integer',
        'affiliate_shoppable_video' => 'integer',
        'affiliate_product_card_gmv' => 'integer',
        'affiliate_products_sold' => 'integer',
        'items_sold' => 'integer',
        'est_commission' => 'integer',
        'avg_order_value' => 'integer',
        'affiliate_orders' => 'integer',
        'product_impressions' => 'integer',
        'avg_affiliate_customers' => 'integer',
        'affiliate_live_streams' => 'integer',
        'open_collaboration_gmv' => 'integer',
        'open_collaboration_est' => 'integer',
        'affiliate_refunded_gmv' => 'integer',
        'affiliate_items_refunded' => 'integer',
        'affiliate_followers' => 'integer',
    ];
}