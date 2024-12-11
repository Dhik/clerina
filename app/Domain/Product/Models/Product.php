<?php

namespace App\Domain\Product\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
