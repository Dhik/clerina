<?php

namespace App\Domain\Talent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TalentPayment extends Model
{
    use HasFactory;

    protected $table = 'talent_payments';

    protected $fillable = [
        'done_payment',
        'talent_id',
        'talent_content_id',
        'status_payment',
        'amount_tf',
        'tanggal_pengajuan',
        'campaign_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'done_payment' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function talent()
    {
        return $this->belongsTo(Talent::class, 'talent_id', 'id');
    }

    public function talentContent()
    {
        return $this->belongsTo(TalentContent::class, 'talent_content_id', 'id');
    }
}
