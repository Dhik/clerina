<?php

namespace App\Domain\Talent\Exports;

use App\Domain\Talent\Models\Talent;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Carbon\Carbon;

class TalentTaxExport implements FromCollection, WithHeadings, WithMapping, WithTitle, ShouldAutoSize, WithStyles, WithEvents, WithStartRow
{
    protected $month;
    protected $year;
    protected $companyNpwp;
    protected $niks;

    public function __construct($month, $year, $companyNpwp, $niks = null)
    {
        $this->month = $month;
        $this->year = $year;
        $this->companyNpwp = $companyNpwp;
        $this->niks = $niks;
    }

    public function collection()
    {
        $talents = Talent::query();

        if ($this->niks) {
            $talents->whereIn('nik', $this->niks);
        }

        $talents = $talents->get();

        $data = collect([
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], 
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''], 
            ['', '', '', '', '', '', '', '', '', '', '', '', '', '', '', ''],
        ]);

        $talentData = $talents->map(function($talent) {
            return [
                '',                  
                $this->month,       
                $this->year,       
                $talent->no_npwp,    
                $talent->no_npwp . '000000000000', 
                'N',                
                '24-104-23',        
                $talent->rate_final,   
                '2',                
                'Other',            
                '20250106',          
                '13/01/2025',     
                $this->companyNpwp . '000000',
                '',              
                '',             
                '',             
            ];
        });

        return $data->concat($talentData);
    }

    public function headings(): array
    {
        return [];
    }

    public function map($row): array
    {
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }
    public function startRow(): int
    {
        return 4;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $event->sheet->mergeCells('A1:B1');
                $event->sheet->setCellValue('A1', 'NPWP Pemotong');
                $event->sheet->setCellValue('C1', $this->companyNpwp);

                $headers = [
                    'B3' => 'Masa Pajak',
                    'C3' => 'Tahun Pajak',
                    'D3' => 'NPWP',
                    'E3' => 'ID TKU Penerima Penghasilan',
                    'F3' => 'Fasilitas',
                    'G3' => 'Kode Objek Pajak',
                    'H3' => 'DPP',
                    'I3' => 'Tarif',
                    'J3' => 'Jenis Dok. Referensi',
                    'K3' => 'Nomor Dok. Referensi',
                    'L3' => 'Tanggal Dok. Referensi',
                    'M3' => 'ID TKU Pemotong',
                    'N3' => 'Opsi Pilihan',
                    'O3' => '',
                    'P3' => '',
                ];

                foreach ($headers as $cell => $value) {
                    $event->sheet->setCellValue($cell, $value);
                }

                $event->sheet->getStyle('B3:P3')->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '5B9BD5']
                    ],
                    'font' => [
                        'color' => ['rgb' => 'FFFFFF'],
                        'bold' => true
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ]
                ]);

                $lastRow = $event->sheet->getHighestRow();

                $event->sheet->getStyle('B4:P' . $lastRow)->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'DDEBF7']
                    ]
                ]);

                $event->sheet->getStyle('B3:P' . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);

                $event->sheet->getStyle('A1')->applyFromArray([
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_LEFT,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'font' => [
                        'bold' => true
                    ]
                ]);

                foreach(range('B','P') as $col) {
                    $event->sheet->getColumnDimension($col)->setAutoSize(true);
                }
            },
        ];
    }

    public function title(): string
    {
        return 'DATA';
    }
}