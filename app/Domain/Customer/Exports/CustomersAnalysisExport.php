<?php
namespace App\Domain\Customer\Exports;

use App\Domain\Customer\Models\CustomersAnalysis;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class CustomersAnalysisExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithColumnFormatting 
{
    protected $month;
    protected $produk;

    // Constructor to accept the filters
    public function __construct($month = null, $produk = null) 
    {
        $this->month = $month;
        $this->produk = $produk;
    }

    // Define the query for fetching the data
    public function query() 
    {
        $query = CustomersAnalysis::query();

        // Apply filters dynamically
        if ($this->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
        }

        if ($this->produk) {
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$this->produk]);
        }

        // Modify the query to resolve ONLY_FULL_GROUP_BY issue
        return $query->select(
            \DB::raw('MIN(id) as id'),
            'nama_penerima', 
            'nomor_telepon', 
            \DB::raw('COUNT(id) as total_orders'), 
            \DB::raw('MIN(is_joined) as is_joined')
        )
        ->groupBy('nama_penerima', 'nomor_telepon');
    }

    // Define the headings for the Excel sheet
    public function headings(): array 
    {
        return [
            'ID', 
            'Nama Penerima', 
            'Nomor Telepon', 
            'Total Orders', 
            'Is Joined'
        ];
    }

    // Map the data to the Excel columns
    public function map($row): array 
    {
        return [
            $row->id, // This corresponds to MIN(id) in the query
            $row->nama_penerima, 
            $row->nomor_telepon, 
            $row->total_orders, 
            $row->is_joined ? 'Joined' : 'Not Joined'
        ];
    }

    // Optional column formatting
    public function columnFormats(): array 
    {
        return [
            'D' => '#,##0', // Formats 'Total Orders' column
        ];
    }
}