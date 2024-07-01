<?php

namespace App\Domain\Customer\Exports;

use App\Domain\Customer\Models\Customer;
use App\Domain\Tenant\Models\Tenant;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomersExport implements FromQuery, ShouldAutoSize, WithColumnFormatting, WithHeadings, WithMapping, WithTitle
{
    use Exportable;

    private int $tenantId;

    const CUSTOM_NUMBER = '#,##0';

    public function __construct(int $tenantId)
    {
        $this->tenantId = $tenantId;
    }

    public function query()
    {
        return Customer::query()
            ->select('customers.*', 'tenants.name as tenant_name')
            ->join('tenants', 'customers.tenant_id', '=', 'tenants.id')
            ->where('customers.tenant_id', $this->tenantId)
            ->orderBy('customers.name', 'asc');
    }

    public function map($row): array
    {
        return [
            $row->name,
            $row->phone_number,
            $row->count_orders,
            $row->tenant_name,
        ];
    }

    public function title(): string
    {
        return 'Customer';
    }

    public function headings(): array
    {
        return [
            trans('labels.name') . ' *',
            trans('labels.phone_number') . ' *',
            trans('labels.total_order'),
            trans('labels.tenant_name') . ' *',
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => '#0',
            'C' => '#0',
            'D' => self::CUSTOM_NUMBER,
        ];
    }
}
