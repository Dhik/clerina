<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tenant\Traits\FilterByTenant;

class CustomersFact extends Model
{
    use FilterByTenant;
    
    protected $table = 'customers_facts';
    
    protected $fillable = [
        'tanggal_pesanan_dibuat',
        'nama_penerima',
        'nomor_telepon',
        'date',
        'total_order',
        'total_qty',
        'alamat',
        'kota_kabupaten',
        'provinsi',
        'talent_id',
        'is_joined',
    ];

    /**
     * Define the relationship to the Tenant model
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'talent_id');
    }

    /**
     * Relationship to CustomersDimension model
     */
    public function customerDimensions()
    {
        return $this->hasMany(CustomersDimension::class, 'id_customers_facts');
    }
}
