<?php

namespace App\Domain\Order\Exports;

use App\Domain\Order\Models\Order;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OrdersExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
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
        return Order::query()
            ->with('salesChannel')
            ->where('tenant_id', $this->tenantId)
            ->where('date', '>=', $this->startDate)
            ->where('date', '<=', $this->endDate)
            ->orderBy('date', 'asc');
    }

    public function map($row): array
    {
        return [
            $row->id_order,
            $row->receipt_number,
            $row->shipment,
            $row->date,
            $row->payment_method,
            $row->product,
            $row->sku,
            $row->variant,
            $row->price,
            $row->qty,
            $row->username,
            $row->customer_name,
            $row->customer_phone_number,
            $row->shipping_address,
            $row->city,
            $row->province,
            $row->salesChannel->name ?? '',
        ];
    }

    public function title(): string
    {
        return 'Order';
    }

    public function headings(): array
    {
        return [
            trans('labels.id_order') . ' *', // A
            trans('labels.receipt_number') . ' *', // B
            trans('labels.shipment'), // C
            trans('labels.date') . ' *', // D
            trans('labels.payment_method'), // E
            trans('labels.product') . ' *', // F
            trans('labels.sku') . ' *', // G
            trans('labels.variant'), // H
            trans('labels.price') . ' *', // I
            trans('labels.qty') . ' *', // J
            trans('labels.username') . ' *', // K
            trans('labels.customer_name'), // L
            trans('labels.phone_number') . ' *', // M
            trans('labels.shipping_address') . ' *', // N
            trans('labels.city'), //O
            trans('labels.province'), // P
            trans('labels.channel'), // Q
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#0',
            'G' => '#0',
            'I' => self::CUSTOM_NUMBER,
            'J' => self::CUSTOM_NUMBER,
            'M' => self::CUSTOM_NUMBER,
        ];
    }
}
