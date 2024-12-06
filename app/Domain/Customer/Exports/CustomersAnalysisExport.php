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

        // Perform grouping and aggregation by kota_kabupaten and produk
        $groupedData = $query->select(
            'kota_kabupaten',    // Grouping by city/district
            'produk',            // Grouping by product
            DB::raw('SUM(qty) as total_orders') // Sum of qty as total orders
        )
        ->groupBy('kota_kabupaten', 'produk') // Group by city/district and product
        ->get();

        // Transform the collection for export
        return $groupedData->map(function ($item) {
            return [
                'kota_kabupaten' => $item->kota_kabupaten,  // City/District
                'produk' => $item->produk,                    // Product
                'total_orders' => $item->total_orders        // Total orders for that product and city/district
            ];
        });
    }

    // Define the headings for the Excel sheet
    public function headings(): array 
    {
        return [
            'Kota/Kabupaten',  // Heading for city/district
            'Produk',          // Heading for product
            'Total Quantity'   // Heading for total orders
        ];
    }

    // Optional column formatting
    public function columnFormats(): array 
    {
        return [
            'C' => '#,##0'  // Format 'Total Quantity' column
        ];
    }
}
