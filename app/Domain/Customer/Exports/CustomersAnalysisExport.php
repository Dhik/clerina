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
use Illuminate\Http\Request;

class CustomersAnalysisExport extends DefaultValueBinder implements FromCollection, WithHeadings, ShouldAutoSize, WithColumnFormatting, WithCustomValueBinder 
{
   protected $month;
   protected $status;
   protected $whichHp;
   protected $cities;

   public function __construct($month = null, $status = null, $whichHp = null, $cities = []) 
   {
       $this->month = $month;
       $this->status = $status;
       $this->whichHp = $whichHp;
       $this->cities = $cities;
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
    
        if ($this->month) {
            $query->whereRaw('DATE_FORMAT(tanggal_pesanan_dibuat, "%Y-%m") = ?', [$this->month]);
        }
        
        if ($this->status) {
            $query->where('status_customer', $this->status);
        }
    
        if ($this->whichHp) {
            $query->where('which_hp', $this->whichHp);
        }
        if (!empty($this->cities)) {
            $query->whereIn('kota_kabupaten', $this->cities);
        }
    
        return $query
            ->select(
                DB::raw('MIN(nama_penerima) as nama'),
                'nomor_telepon as kontak',
                DB::raw('NULL as email'),
                DB::raw('MIN(alamat) as alamat'),
                DB::raw('NULL as kecamatan'),
                DB::raw('MIN(kota_kabupaten) as kota'),
                DB::raw('MIN(provinsi) as provinsi'),
                DB::raw('NULL as kode_pos'),
                DB::raw('MIN(status_customer) as `group_customer`'),
                DB::raw('MIN(which_hp) as hp_mana'),
                DB::raw('NULL as note'),
                DB::raw('NULL as user_terkait'),
                DB::raw('NULL as birthday')
            )
            ->groupBy('nomor_telepon')
            ->get();
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