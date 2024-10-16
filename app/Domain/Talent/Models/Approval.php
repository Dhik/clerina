<?php

namespace App\Domain\Talent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Domain\Tenant\Traits\FilterByTenant;

class Approval extends Model
{
    protected $table = 'approvals';

    protected $fillable = [
        'name',
        'photo',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
