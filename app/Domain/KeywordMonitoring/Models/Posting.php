<?php

namespace App\Domain\KeywordMonitoring\Models;

use Illuminate\Database\Eloquent\Model;

class Posting extends Model
{
    protected $fillable = [
        'play_count',
        'comment_count',
        'digg_count',
        'share_count',
        'collect_count',
        'download_count',
        'aweme_id',
        'keyword_id',
        'username',
        'upload_date',
    ];
}
