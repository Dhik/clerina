<?php

namespace App\Domain\Order\Models;

use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'date',
        'id_order',
        'sales_channel_id',
        'customer_name',
        'customer_phone_number',
        'product',
        'qty',
        'receipt_number',
        'shipment',
        'payment_method',
        'sku',
        'variant',
        'price',
        'username',
        'shipping_address',
        'city',
        'province',
        'amount',
        'tenant_id',
    ];

    /**
     * Retrieve the model for a bound value.
     *
     * @param  mixed  $value
     * @param  string|null  $field
     */
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        // Customize the query to retrieve the model based on your requirements
        return $this->where('id', $value)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();
    }

    /**
     * Casting date
     */
    public function getDateAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * Relations to Sales Channel
     */
    public function salesChannel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class);
    }

    /**
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
