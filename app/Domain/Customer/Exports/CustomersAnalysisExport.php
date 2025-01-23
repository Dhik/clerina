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
   protected $status;
   protected $whichHp;

   public function __construct($month = null, $status = null, $whichHp = null) 
   {
       $this->month = $month;
       $this->status = $status;
       $this->whichHp = $whichHp;
   }

   public function collection() 
   {
       $query = CustomersAnalysis::query();

       if ($this->month) {
           $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
       }
       
       if ($this->status) {
           $query->where('status_customer', $this->status);
       }

       if ($this->whichHp) {
           $query->where('which_hp', $this->whichHp);
       }

       return $query->select(
           'nama_penerima as nama',
           DB::raw("CONCAT('\"', nomor_telepon, '\"') as kontak"),
           DB::raw('NULL as email'),
           'alamat',
           DB::raw('NULL as kecamatan'),
           'kota_kabupaten as kota',
           'provinsi',
           DB::raw('NULL as kode_pos'),
           'status_customer as group',
           DB::raw('NULL as tag'),
           DB::raw('NULL as note'),
           DB::raw('NULL as user_terkait'),
           DB::raw('NULL as birthday')
       )->get();
   }

   public function headings(): array 
   {
       return [
           'Nama',
           'Kontak', 
           'Email',
           'Alamat',
           'Kecamatan',
           'Kota',
           'Provinsi',
           'Kode Pos',
           'Group',
           'Tag',
           'Note',
           'User Terkait',
           'Birthday'
       ];
   }

   public function columnFormats(): array 
   {
       return [];
   }
}