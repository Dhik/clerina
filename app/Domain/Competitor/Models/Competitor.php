<?php

namespace App\Domain\Competitor\Models;

use Illuminate\Database\Eloquent\Model;

class Competitor extends Model
{
    protected $fillable = [
        'brand',
        'keterangan',
        'channel',
        'omset',
        'periode',
        'type'
    ];
}
