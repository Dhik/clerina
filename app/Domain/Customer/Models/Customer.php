<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'phone_number',
        'count_orders',
        'tenant_id',
    ];

    protected $appends = [
      'count_orders_formatted',
      'wa_link'
    ];

    /**
     * Relation to customer note
     */
    public function customerNotes(): HasMany
    {
        return $this->hasMany(CustomerNote::class);
    }

    /**
     * Relation to tenant
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Return formatted count orders
     */
    public function getCountOrdersFormattedAttribute(): string
    {
        return number_format($this->count_orders, 0, ',', '.');
    }

    /**
     * Return link WhatsApp
     */
    public function getWaLinkAttribute(): string
    {
        $phoneNumber = $this->phone_number;

        // Check if phone number starts with '0'
        if (str_starts_with($phoneNumber, '0')) {
            // Replace '0' with '62'
            $phoneNumber = '62' . substr($phoneNumber, 1);
        }

        return 'https://wa.me/' . $phoneNumber;
    }
}
