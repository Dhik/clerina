<?php

namespace App\Domain\Order\Models;

use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyHpp extends Model
{
    protected $table = 'daily_hpp';
    
    protected $fillable = [
        'date',
        'sales_channel_id',
        'sku',
        'quantity',
        'HPP',
        'tenant_id',
    ];
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('id', $value)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();
    }
    public function getDateAttribute($value): ?string
    {
        return $value ? Carbon::parse($value)->format('d M Y') : null;
    }
    public function salesChannel(): BelongsTo
    {
        return $this->belongsTo(SalesChannel::class);
    }
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
    public function product()
    {
        return $this->belongsTo(Product::class, 'sku', 'sku');
    }
}