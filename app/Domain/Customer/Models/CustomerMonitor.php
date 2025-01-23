<?php

namespace App\Domain\Customer\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Tenant\Traits\FilterByTenant;

    
class CustomerMonitor extends Model
{
    use FilterByTenant;
    protected $table = 'customer_monitor';
    protected $fillable = [
        'date',
        'status',
        'count_customer',
        'tenant_id',
    ];
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
}
