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

    // Constructor to accept the filters
    public function __construct($month = null, $produk = null) 
    {
        $this->month = $month;
        $this->produk = $produk;
    }

    // Use collection instead of query to have more control
    public function collection() 
    {
        // Start with base query
        $query = CustomersAnalysis::query();

        // Apply filters dynamically
        if ($this->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
        }

        if ($this->produk) {
            $query->where('produk', 'LIKE', $this->produk . '%');
        }

        // Perform grouping and aggregation
        $groupedData = $query->select(
            DB::raw('MIN(id) as id'),
            'nama_penerima',
            'nomor_telepon',
            DB::raw('SUM(qty) as total_orders'),
            DB::raw('MAX(is_joined) as is_joined'),
            DB::raw('COUNT(DISTINCT produk) as unique_products')
        )
        ->groupBy('nama_penerima', 'nomor_telepon')
        ->get();

        // Transform the collection for export
        return $groupedData->map(function ($item) {
            return [
                'id' => $item->id,
                'nama_penerima' => $item->nama_penerima,
                'nomor_telepon' => $item->nomor_telepon,
                'total_orders' => $item->total_orders,
                'unique_products' => $item->unique_products,
                'is_joined' => $item->is_joined ? 'Joined' : 'Not Joined'
            ];
        });
    }

    // Define the headings for the Excel sheet
    public function headings(): array 
    {
        return [
            'ID', 
            'Nama Penerima', 
            'Nomor Telepon', 
            'Total Quantity',
            'Unique Products',
            'Is Joined'
        ];
    }

    // Optional column formatting
    public function columnFormats(): array 
    {
        return [
            'D' => '#,##0', // Formats 'Total Quantity' column
            'E' => '#,##0'  // Formats 'Unique Products' column
        ];
    }
}
