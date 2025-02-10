<?php
namespace App\Domain\Customer\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Facades\DB;
use App\Domain\Customer\Models\CustomersAnalysis;

class CustomersAnalysisExportMonth implements FromCollection, WithHeadings 
{
    public function collection() 
    {
        return CustomersAnalysis::query()
            ->whereBetween('tanggal_pesanan_dibuat', ['2025-01-01', '2025-01-05'])
            ->select([
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
}