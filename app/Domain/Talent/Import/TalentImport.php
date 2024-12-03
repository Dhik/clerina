<?php

namespace App\Domain\Talent\Import;

use App\Domain\Talent\Models\Talent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
class TalentImport implements ToCollection, SkipsEmptyRows, WithMapping, WithStartRow, WithUpserts, WithValidation
{
    use Importable;

    /**
     * Define the unique field for upsert operations.
     */
    public function uniqueBy(): string
    {
        return 'username';
    }

    /**
     * Define the starting row for the import.
     */
    public function startRow(): int
    {
        return 2; // Assuming the first row is the header
    }

    /**
     * Map each row from the Excel file to the corresponding fields in the database.
     */
    public function map($row): array
{
    return [
        'username' => $row[0] ?? '',
        'talent_name' => $row[1] ?? '',
        'video_slot' => $row[2] ?? 0,
        'content_type' => $row[3] ?? '',
        'produk' => $row[4] ?? '',
        'rate_final' => $row[5] ?? 0,
        'pic' => $row[6] ?? '',
        'bulan_running' => $row[7] ?? '',
        'niche' => $row[8] ?? '',
        'followers' => $row[9] ?? 0,
        'address' => $row[10] ?? '',
        'phone_number' => $row[11] ?? '',
        'bank' => $row[12] ?? '',
        'no_rekening' => $row[13] ?? '',
        'nama_rekening' => $row[14] ?? '',
        'no_npwp' => $row[15] ?? '',
        'pengajuan_transfer_date' => $row[16] ?? null,
        'gdrive_ttd_kol_accepting' => $row[17] ?? '',
        'nik' => $row[18] ?? '',
        'price_rate' => $row[19] ?? 0,
        'first_rate_card' => $row[20] ?? 0,
        'discount' => $row[21] ?? 0,
        'slot_final' => $row[22] ?? 0,
        'tax_deduction' => $row[23] ?? 0,
    ];
}

    /**
     * Handle the collection of rows from the Excel file.
     */
    public function collection(Collection $rows)
{
    foreach ($rows as $row) {
        $data = [
            'username' => $row['username'] ?? '',
            'talent_name' => $row['talent_name'] ?? '',
            'video_slot' => $row['video_slot'] ?? 0,
            'content_type' => $row['content_type'] ?? '',
            'produk' => $row['produk'] ?? '',
            'rate_final' => $row['rate_final'] ?? 0,
            'pic' => $row['pic'] ?? '',
            'bulan_running' => $row['bulan_running'] ?? '',
            'niche' => $row['niche'] ?? '',
            'followers' => $row['followers'] ?? 0,
            'address' => $row['address'] ?? '',
            'phone_number' => $row['phone_number'] ?? '',
            'bank' => $row['bank'] ?? '',
            'no_rekening' => $row['no_rekening'] ?? '',
            'nama_rekening' => $row['nama_rekening'] ?? '',
            'no_npwp' => $row['no_npwp'] ?? '',
            'pengajuan_transfer_date' => $this->formatDateForDatabase($row['pengajuan_transfer_date'] ?? null),
            'gdrive_ttd_kol_accepting' => $row['gdrive_ttd_kol_accepting'] ?? '',
            'nik' => $row['nik'] ?? '',
            'price_rate' => $row['price_rate'] ?? 0,
            'first_rate_card' => $row['first_rate_card'] ?? 0,
            'discount' => $row['discount'] ?? 0,
            'slot_final' => $row['slot_final'] ?? 0,
            'tax_deduction' => $row['tax_deduction'] ?? 0,
            'tenant_id' => 1,
            'tax_percentage' => 0,
        ];
        Talent::create($data);
    }
}


    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'username' => 'nullable|string|max:255',
            'talent_name' => 'nullable|string|max:255',
            'video_slot' => 'nullable|integer',
            'content_type' => 'nullable|string|max:255',
            'produk' => 'nullable|string|max:255',
            'rate_final' => 'nullable|integer',
            'pic' => 'nullable|string|max:255',
            'bulan_running' => 'nullable|string|max:255',
            'niche' => 'nullable|string|max:255',
            'followers' => 'nullable|integer',
            'address' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:255',
            'bank' => 'nullable|string|max:255',
            'no_rekening' => 'nullable',
            'nama_rekening' => 'nullable|string|max:255',
            'no_npwp' => 'nullable',
            'pengajuan_transfer_date' => 'nullable|date',
            'gdrive_ttd_kol_accepting' => 'nullable|string|max:255',
            'nik' => 'nullable',
            'price_rate' => 'nullable|integer',
            'first_rate_card' => 'nullable|integer',
            'discount' => 'nullable|integer',
            'slot_final' => 'nullable|integer',
            'tax_deduction' => 'nullable|integer',
        ];        
    }
    protected function formatDateForDatabase($dateString)
    {
        try {
            if (is_numeric($dateString)) {
                $formattedDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateString);
                return $formattedDate->format('Y-m-d');
            }
            
            return Carbon::parse($dateString)->format('Y-m-d');
            
        } catch (Exception $e) {
            throw new Exception("Date format not recognized or invalid: $dateString");
        }
    }

}
