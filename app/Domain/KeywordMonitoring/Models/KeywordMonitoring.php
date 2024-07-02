<?php

namespace App\Domain\KeywordMonitoring\Models;

use Illuminate\Database\Eloquent\Model;
use App\Domain\KeywordMonitoring\Models\Posting;

class KeywordMonitoring extends Model
{
    protected $fillable = [
        'id',
        'keyword',
    ];
    public function postings()
    {
        return $this->hasMany(Posting::class, 'keyword_id', 'id');
    }
}
