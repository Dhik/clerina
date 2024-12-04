<?php

namespace App\Domain\Talent\Exports;

use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use App\Domain\Talent\Models\TalentContent;
use Yajra\DataTables\Utilities\Request;

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
        ];
    }

    public function map($talentContent): array
    {
        return [
            $talentContent->transfer_date,
            $talentContent->talent ? $talentContent->talent->username : 'N/A',
            $talentContent->talent->slot_final,
            $talentContent->pic_code,
            $talentContent->talent->content_type,
            $talentContent->talent->produk,
            $talentContent->talent->rate_final,
            $talentContent->dealing_upload_date,
            $talentContent->posting_date,
            $talentContent->done ? 'Yes' : 'No',
            $talentContent->upload_link,
            $talentContent->pic_code,
            $talentContent->boost_code,
            $talentContent->talent->bulan_running,
            $talentContent->kerkun ? 'Kerkun' : 'Non Kerkun',
        ];
    }
}
