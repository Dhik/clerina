<?php

namespace App\Domain\Employee\Import;

use App\Domain\Customer\BLL\Customer\CustomerBLL;
use App\Domain\Employee\Models\Payroll;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class PayrollImport implements SkipsEmptyRows, ToModel, WithMapping, WithStartRow, WithUpserts, WithValidation
{
    use Importable;

    protected array $importedData = [];
    public function __construct(){
        //
    }
    public function batchSize(): int {
        return 1000;
    }
    public function uniqueBy(): string {
        return 'id';
    }
    public function map($row): array {
        return [
            'employee_id' => $row[0],
            'full_name' => $row[1],
            'gaji_pokok' => $row[2],
            'tunjangan_jabatan' => $row[3],
            'insentif_live' => $row[4],
            'insentif' => $row[5],
            'function' => $row[6],
            'bpjs' => $row[7]
        ];
    }
    public function startRow(): int {
        return 2;
    }
    public function model(array $row): void {
        $payroll = Payroll::updateOrCreate([
            'employee_id' => $row['employee_id'],
            'full_name' => $row['full_name'],
            'gaji_pokok' => $row['gaji_pokok'],
            'tunjangan_jabatan' => $row['tunjangan_jabatan'],
            'insentif_live' => $row['insentif_live'],
            'insentif' => $row['insentif'],
            'function' => $row['function'],
            'BPJS' => $row['bpjs'],
        ]);
        $this->importedData[] = $payroll;
    }
    public function getImportedData(): array {
        return $this->importedData;
    }
    public function rules(): array {
        return [
            'employee_id' => 'max:255',
            'full_name' => 'max:255',
            'gaji_pokok' => 'numeric|integer',
            'tunjangan_jabatan' => 'numeric|integer',
            'insentif_live' => 'numeric|integer',
            'insentif' => 'numeric|integer',
            'function' => 'numeric|integer',
            'bpjs' => 'numeric|integer',
        ];
    }
}