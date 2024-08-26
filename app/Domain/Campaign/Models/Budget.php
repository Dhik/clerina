<?php

namespace App\Domain\Campaign\Models;

use App\Domain\Tenant\Traits\FilterByTenant;
use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use App\Domain\Campaign\Models\Campaign;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Budget extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;

    protected $fillable = [
        'nama_budget',
        'budget'
    ];

    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'id_budget', 'id');
    }
}