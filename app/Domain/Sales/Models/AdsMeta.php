<?php

namespace App\Domain\Sales\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class AdsMeta extends Model
{
    protected $table = 'ads_meta';

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
    ];

    protected $casts = [
        'date' => 'date',
        'amount_spent' => 'integer',
        'impressions' => 'integer',
        'content_views_shared_items' => 'float',
        'adds_to_cart_shared_items' => 'float',
        'purchases_shared_items' => 'float',
        'purchases_conversion_value_shared_items' => 'float'
    ];
}
