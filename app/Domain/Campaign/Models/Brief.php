<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Brief extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;

    protected $fillable = [
        'acc_date',
        'title',
        'brief',
        'tenant_id',
    ];
}