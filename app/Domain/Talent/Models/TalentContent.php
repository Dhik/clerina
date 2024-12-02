<?php

namespace App\Domain\Talent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Domain\Campaign\Models\Campaign;

class TalentContent extends Model
{
    use HasFactory;

    protected $table = 'talent_content';

    protected $fillable = [
        'transfer_date',
        'talent_id',
        'dealing_upload_date',
        'posting_date',
        'campaign_id',
        'done',
        'upload_link',
        'pic_code',
        'boost_code',
        'kerkun',
        'final_rate_card',
        'product',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'transfer_date' => 'date',
        'dealing_upload_date' => 'date',
        'posting_date' => 'date',
        'done' => 'boolean',
        'kerkun' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    public function talent()
    {
        return $this->belongsTo(Talent::class, 'talent_id', 'id');
    }
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }
}
