<?php

namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\InteractsWithMedia;

class OperationalSpent extends Model
{
    use FilterByTenant, InteractsWithMedia;
    protected $fillable = [
        'spent',
        'month',
        'year',
        'tenant_id',
    ];
}
