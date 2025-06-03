<?php

// 1. FIXED OperationalSpent Model
namespace App\Domain\Sales\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class OperationalSpent extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;
    
    protected $fillable = [
        'spent',
        'month',
        'year',
        'tenant_id',
    ];

    protected $casts = [
        'spent' => 'decimal:2',
        'month' => 'integer',
        'year' => 'integer',
        'tenant_id' => 'integer'
    ];

    // Required method for HasMedia interface
    public function registerMediaCollections(): void
    {
        // Define media collections if needed
        // Example: $this->addMediaCollection('receipts')->singleFile();
    }
}
