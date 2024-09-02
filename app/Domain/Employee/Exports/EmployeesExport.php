<?php

namespace App\Domain\Employee\Exports;

use App\Domain\Employee\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeesExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Employee::select('full_name', 'nik_npwp_16_digit', 'birth_place', 'birth_date', 'citizen_id_address', 'mobile_phone', 'email')->get();
    }

    public function headings(): array
    {
        return [
            'Nama',
            'NIK',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Alamat',
            'No HP',
            'Email',
        ];
    }
}
