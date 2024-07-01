<?php

namespace App\Domain\Marketing\Exports;

use App\Domain\Marketing\Models\Marketing;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MarketingExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithStyles, WithTitle
{
    use Exportable;

    private string $startDate;

    private string $endDate;

    const CUSTOM_NUMBER = '#,##0';

    public function __construct(protected int $tenantId)
    {
    }

    public function forPeriod(string $date): static
    {
        [$startDateString, $endDateString] = explode(' - ', $date);
        $this->startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
        $this->endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

        return $this;
    }

    public function query()
    {
        return Marketing::query()
            ->with('marketingCategory', 'marketingSubCategory')
            ->where('tenant_id', $this->tenantId)
            ->where('date', '>=', $this->startDate)
            ->where('date', '<=', $this->endDate);
    }

    public function map($row): array
    {
        return [
            $row->date,
            ucfirst($row->type),
            $row->marketingCategory->name ?? '',
            $row->marketingSubCategory->name ?? '',
            $row->amount,
        ];
    }

    public function title(): string
    {
        return 'Marketing';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        return [
            trans('labels.date'),
            trans('labels.type'),
            trans('labels.category'),
            trans('labels.sub_category'),
            trans('labels.amount'),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_TEXT,
            'E' => self::CUSTOM_NUMBER,
        ];
    }
}
