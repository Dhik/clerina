<?php

namespace App\Domain\Sales\Models;

use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SalesChannel extends Model
{
    protected $fillable = [
        'name',
    ];

    /**
     * Relations to Order
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Relations to AdSpentMarketPlaceDAL
     */
    public function adSpentMarketPlace(): HasMany
    {
        return $this->hasMany(AdSpentMarketPlace::class);
    }

    /**
     * Relations to Visit
     */
    public function visit(): HasMany
    {
        return $this->hasMany(Visit::class);
    }
}
