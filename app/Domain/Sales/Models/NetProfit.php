<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Models\Tenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NetProfit extends Model
{
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
     * relations to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
