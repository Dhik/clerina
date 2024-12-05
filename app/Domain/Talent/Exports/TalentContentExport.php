<?php
namespace App\Domain\Talent\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Domain\Talent\Models\TalentContent;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Str;

class TalentContentExport implements FromQuery, WithHeadings, WithMapping
{
    protected $startDate;
    protected $endDate;

    public function __construct(Request $request)
    {
        $dateRange = $request->input('filterPostingDate');
        
        if ($dateRange) {
            $dates = explode(' - ', $dateRange);
            $this->startDate = $dates[0];
            $this->endDate = $dates[1];
        }
    }

    public function query()
    {
        $query = TalentContent::query()->with('talent');

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('posting_date', [$this->startDate, $this->endDate]);
        }
        return $query;
    }

    public function headings(): array
    {
        return [
            'Tanggal Transfer',
            'Akun',
            'Slot',
            'PIC',
            'Jenis Konten',
            'Produk',
            'RC Final',
            'Tanggal Dealing Upload',
            'Tanggal Posting',
            'Done',
            'Link Posting',
            'Kode PIC',
            'Kode Boost',
            'Running di Bulan',
            'Kerkun dan Non Kerkun',
            'Talent Should Get',
        ];
    }

    public function map($talentContent): array
    {
        $talentShouldGet = $this->calculateTalentShouldGet($talentContent);
        
        return [
            $talentContent->transfer_date,
            $talentContent->talent ? $talentContent->talent->username : 'N/A',
            $talentContent->talent ? $talentContent->talent->slot_final : 'N/A',
            $talentContent->pic_code,
            $talentContent->talent ? $talentContent->talent->content_type : 'N/A',
            $talentContent->talent ? $talentContent->talent->produk : 'N/A',
            $talentContent->talent ? $talentContent->talent->rate_final : 'N/A',
            $talentContent->dealing_upload_date,
            $talentContent->posting_date,
            $talentContent->done ? 'Yes' : 'No',
            $talentContent->upload_link,
            $talentContent->pic_code,
            $talentContent->boost_code,
            $talentContent->talent ? $talentContent->talent->bulan_running : 'N/A',
            $talentContent->kerkun ? 'Kerkun' : 'Non Kerkun',
            $talentShouldGet,
        ];
    }

    protected function calculateTalentShouldGet($talentContent)
    {
        if (!is_null($talentContent->upload_link)) {
            $rateFinal = $talentContent->talent ? $talentContent->talent->rate_final : 0; 
            $slotFinal = $talentContent->talent ? $talentContent->talent->slot_final : 1;
            $accountName = $talentContent->talent ? $talentContent->talent->nama_rekening : '';
            
            $totalPerSlot = $slotFinal > 0 ? $rateFinal / $slotFinal : 0;
            return $this->adjustSpentForTax($totalPerSlot, $accountName);
        }
        return 0;
    }

    protected function adjustSpentForTax($spent, $accountName)
    {
        $isPTorCV = Str::startsWith($accountName, ['PT', 'CV']);
        $pph = $isPTorCV ? $spent * 0.02 : $spent * 0.025;
        return intval($spent - $pph);
    }
}
