<?php

namespace App\Domain\Customer\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerNote extends Model
{
    use FilterByTenant;

    protected $fillable = [
        'note',
        'user_id',
        'customer_id',
        'tenant_id',
    ];

    /**
     * Casting date
     */
    public function getCreatedAtAttribute($value): string
    {
        return Carbon::parse($value)->format('d M Y');
    }

    /**
     * Relation to customer
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Relation to user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
