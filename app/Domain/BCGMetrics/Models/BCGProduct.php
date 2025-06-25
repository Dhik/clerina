<?php

namespace App\Domain\BCGMetrics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BcgProduct extends Model
{
    use HasFactory;

    protected $table = 'bcg_product';

    protected $fillable = [
        'date',
        'tenant_id',
        'kode_produk',
        'nama_produk',
        'visitor',
        'jumlah_atc',
        'jumlah_pembeli',
        'qty_sold',
        'sales',
    ];

    protected $casts = [
        'date' => 'date',
        'tenant_id' => 'integer',
        'visitor' => 'integer',
        'jumlah_atc' => 'integer',
        'jumlah_pembeli' => 'integer',
        'qty_sold' => 'integer',
        'sales' => 'integer',
    ];
}