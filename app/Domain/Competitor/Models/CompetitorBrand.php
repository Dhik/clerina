<?php

namespace App\Domain\Competitor\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitorBrand extends Model
{
    protected $fillable = [
        'brand',
        'keterangan',
        'logo',
    ];
}
