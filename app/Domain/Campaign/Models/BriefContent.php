<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\Campaign\Models\Brief;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class BriefContent extends Model implements HasMedia
{
    use InteractsWithMedia;

    protected $fillable = [
        'id_brief', 'link','tenant_id',
    ];
    public function brief(): BelongsTo
    {
        return $this->belongsTo(Brief::class, 'id_brief');
    }
}