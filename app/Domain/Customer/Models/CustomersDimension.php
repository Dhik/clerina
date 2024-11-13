<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tenant\Traits\FilterByTenant;

class CustomersDimension extends Model
{

    protected $table = 'customers_dimensions';
    
    protected $fillable = [
        'id_customers_facts',
        'produk',
        'qty',
        'tanggal_pesanan_dibuat',
    ];

    /**
     * Define the relationship to the CustomersFact model
     */
    public function customersFact()
    {
        return $this->belongsTo(CustomersFact::class, 'id_customers_facts');
    }

    /**
     * Define the relationship to the Tenant model
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id');
    }
}
