<?php
namespace App\Domain\Customer\Exports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Facades\DB;
use App\Domain\Customer\Models\CustomersAnalysis;
use Illuminate\Http\Request;

class CustomersAnalysisExport implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting 
{
    protected $month;
    protected $produk;

    public function __construct($month = null, $produk = null) 
    {
        $this->month = $month;
        $this->produk = $produk;
    }

    public function collection() 
    {
        $query = CustomersAnalysis::query();
        
        // Filter by month if provided
        if ($this->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
        }

        // Filter by product if provided
        if ($this->produk) {
            $query->where('produk', 'LIKE', $this->produk . '%');
        }

        // Perform grouping and aggregation by kota_kabupaten only
        $groupedData = $query->select(
            'kota_kabupaten', // Grouping by city/district
            DB::raw('SUM(qty) as total_orders'),
            DB::raw('COUNT(DISTINCT produk) as unique_products')
        )
        ->groupBy('kota_kabupaten')
        ->get();

        // Transform the collection for export
        return $groupedData->map(function ($item) {
            return [
                'kota_kabupaten' => $item->kota_kabupaten,  // Only include the city/district
                'total_orders' => $item->total_orders,       // Total orders per city/district
                'unique_products' => $item->unique_products  // Unique products per city/district
            ];
        });
    }

    // Define the headings for the Excel sheet
    public function headings(): array 
    {
        return [
            'Kota/Kabupaten', // Heading for city/district
            'Total Quantity',  // Heading for total orders
            'Unique Products'  // Heading for unique products
        ];
    }

    // Optional column formatting
    public function columnFormats(): array 
    {
        return [
            'B' => '#,##0', // Formats 'Total Quantity' column
            'C' => '#,##0'  // Formats 'Unique Products' column
        ];
    }
}
