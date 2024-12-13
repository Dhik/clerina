<?php

namespace App\Domain\Product\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Order\Models\Order;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product',
        'stock',
        'sku',
        'harga_jual',
        'harga_markup',
        'harga_cogs',
        'harga_batas_bawah',
        'tenant_id',
    ];
    public function orders() 
    {
        return $this->hasMany(Order::class, 'sku', 'sku');
    }
}
