<?php

namespace App\Domain\Report\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $table = 'reports';
    protected $fillable = [
        'type', 
        'platform', 
        'link', 
        'month',
        'thumbnail',
        'title',
        'description'
    ];
}
