<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomersAnalysis extends Model
{
    protected $table = 'customers_analysis';
    protected $fillable = [
        'tanggal_pesanan_dibuat',
        'nama_penerima',
        'produk',
        'qty',
        'alamat',
        'kota_kabupaten',
        'provinsi',
        'nomor_telepon',
        'tenant_id',
        'sales_channel_id',
        'is_joined',
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Example relationship to a SalesChannel model
     */
    public function salesChannel()
    {
        return $this->belongsTo(SalesChannel::class, 'sales_channel_id');
    }
}
