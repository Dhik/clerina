<?php

namespace App\Domain\Talent\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use App\Domain\Tenant\Traits\FilterByTenant;

class Talent extends Model implements HasMedia
{
    use FilterByTenant, InteractsWithMedia;
    protected $table = 'talents';
    protected $fillable = [
        'username',
        'video_slot',
        'content_type',
        'produk',
        'platform',
        'rate_final',
        'pic',
        'bulan_running',
        'niche',
        'followers',
        'talent_name',
        'address',
        'phone_number',
        'bank',
        'no_rekening',
        'nama_rekening',
        'no_npwp',
        'pengajuan_transfer_date',
        'gdrive_ttd_kol_accepting',
        'nik',
        'price_rate',
        'first_rate_card',
        'discount',
        'slot_final',
        'tax_deduction',
        'amount_tf',
        'scope_of_work',
        'masa_kerjasama',
        'tenant_id',
        'dp_amount',
        'rate_per_slot',
        'tax_percentage',
    ];
    public function talentContents()
    {
        return $this->hasMany(TalentContent::class, 'talent_id', 'id');
    }
    public function talentPayments()
    {
        return $this->hasMany(TalentPayment::class, 'talent_id', 'id');
    }
}
