<?php

namespace App\Domain\Tenant\Models;

use App\Domain\User\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tenant extends Model
{
    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Relation to user
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
