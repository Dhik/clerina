<?php
namespace App\Domain\Customer\Exports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Facades\DB;
use App\Domain\Customer\Models\CustomersAnalysis;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;

class CustomersAnalysisExportMonth extends DefaultValueBinder implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder 
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function bindValue(\PhpOffice\PhpSpreadsheet\Cell\Cell $cell, $value)
    {
        if ($cell->getColumn() === 'B') {
            $cell->setValueExplicit($value, DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell, $value);
    }

    public function collection() 
    {
        $query = CustomersAnalysis::query();

        // Apply filters
        if (!empty($this->filters['month'])) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->filters['month']]);
        }

        if (!empty($this->filters['status'])) {
            $query->where('status_customer', $this->filters['status']);
        }

        if (!empty($this->filters['which_hp'])) {
            $query->where('which_hp', $this->filters['which_hp']);
        }

        if (!empty($this->filters['start_date'])) {
            $query->whereDate('tanggal_pesanan_dibuat', '>=', $this->filters['start_date']);
        }

        if (!empty($this->filters['end_date'])) {
            $query->whereDate('tanggal_pesanan_dibuat', '<=', $this->filters['end_date']);
        }

        if (!empty($this->filters['sales_channel'])) {
            $query->where('sales_channel_id', $this->filters['sales_channel']);
        }

        if (!empty($this->filters['social_media'])) {
            $query->where('social_media_id', $this->filters['social_media']);
        }

        if (isset($this->filters['is_joined'])) {
            $query->where('is_joined', $this->filters['is_joined']);
        }

        return $query->select([
            'nama_penerima',
            'nomor_telepon',
            'produk',
            'qty',
            'alamat',
            'kota_kabupaten',
            'provinsi',
            'status_customer',
            'which_hp',
            'sales_channel_id',
            'social_media_id',
            'is_joined',
            DB::raw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m-%d") as tanggal_order')
        ])->get();
    }

    public function headings(): array 
    {
        return [
            'Nama Penerima',
            'Nomor Telepon',
            'Produk',
            'Quantity',
            'Alamat',
            'Kota/Kabupaten',
            'Provinsi',
            'Status Customer',
            'Which HP',
            'Sales Channel ID',
            'Social Media ID',
            'Is Joined',
            'Tanggal Order'
        ];
    }

    public function columnFormats(): array 
    {
        return [];
    }
}