<?php

namespace App\Domain\Competitor\Models;

use Illuminate\Database\Eloquent\Model;

class CompetitorSales extends Model
{
    protected $fillable = [
        'competitor_brand_id',
        'channel',
        'omset',
        'date',
        'type',
    ];

    public function competitorBrand()
    {
        return $this->belongsTo(CompetitorBrand::class);
    }
}
