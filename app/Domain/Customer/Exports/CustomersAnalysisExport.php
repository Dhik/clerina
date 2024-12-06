<?php
namespace App\Domain\Customer\Exports;

use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Illuminate\Support\Facades\DB;
use App\Domain\Customer\Models\CustomersAnalysis;

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
        $query = CustomersAnalysis::query();

        // Apply filters dynamically
        if ($this->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
        }

        if ($this->produk) {
            $query->whereRaw('SUBSTRING_INDEX(produk, " -", 1) = ?', [$this->produk]);
        }

        // Use DB::raw to create a subquery that handles grouping
        $groupedData = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query->getQuery())
            ->select(
                DB::raw('MIN(id) as id'),
                'nama_penerima', 
                'nomor_telepon', 
                DB::raw('COUNT(*) as total_orders'), 
                DB::raw('MIN(is_joined) as is_joined')
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
            'Total Orders', 
            'Is Joined'
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