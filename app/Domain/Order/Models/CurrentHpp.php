<?php

namespace App\Domain\Order\Models;

use App\Domain\Tenant\Models\Tenant;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CurrentHpp extends Model
{
    protected $table = 'current_hpp';
    
    protected $fillable = [
        'sku',
        'hpp',
        'tenant_id',
    ];
    public function resolveRouteBinding($value, $field = null): ?Model
    {
        return $this->where('id', $value)
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->first();
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