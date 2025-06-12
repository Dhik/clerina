<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;

class LaporanKeuangan extends Model
{
    use FilterByTenant, InteractsWithMedia;

    protected $table = 'laporan_keuangan';
    protected $fillable = [
        'date',
        'sales',
        'marketing',
        'spent_kol',
        'affiliate',
        'operasional',
        'hpp',
        'roas',
        'visit',
        'qty',
        'order',
        'closing_rate',
        'tenant_id',
        'crm_sales',
        'b2b_sales',
        'balance_amount',
        'gross_revenue', 
        'fee_admin',
        'count_id_order'
    ];
    protected $casts = [
        'date' => 'date'
    ];
}
