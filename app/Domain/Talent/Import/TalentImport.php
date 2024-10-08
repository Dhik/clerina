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
            'username' => $row[0],
            'talent_name' => $row[1],
            'video_slot' => $row[2],
            'content_type' => $row[3],
            'produk' => $row[4],
            'rate_final' => $row[5],
            'pic' => $row[6],
            'bulan_running' => $row[7],
            'niche' => $row[8],
            'followers' => $row[9],
            'address' => $row[10],
            'phone_number' => $row[11],
            'bank' => $row[12],
            'no_rekening' => $row[13],
            'nama_rekening' => $row[14],
            'no_npwp' => $row[15],
            'pengajuan_transfer_date' => $row[16],
            'gdrive_ttd_kol_accepting' => $row[17],
            'nik' => $row[18],
            'price_rate' => $row[19],
            'first_rate_card' => $row[20],
            'discount' => $row[21],
            'slot_final' => $row[22],
            'tax_deduction' => $row[23],
        ];
    }

    /**
     * Handle the collection of rows from the Excel file.
     */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            $existingTalent = Talent::where('username', $row['username'])->first();

            if ($existingTalent) {
                // Update the existing talent
                $existingTalent->update([
                    'talent_name' => $row['talent_name'],
                    'video_slot' => $row['video_slot'],
                    'content_type' => $row['content_type'],
                    'produk' => $row['produk'],
                    'rate_final' => $row['rate_final'],
                    'pic' => $row['pic'],
                    'bulan_running' => $row['bulan_running'],
                    'niche' => $row['niche'],
                    'followers' => $row['followers'],
                    'address' => $row['address'],
                    'phone_number' => $row['phone_number'],
                    'bank' => $row['bank'],
                    'no_rekening' => $row['no_rekening'],
                    'nama_rekening' => $row['nama_rekening'],
                    'no_npwp' => $row['no_npwp'],
                    'pengajuan_transfer_date' => $this->formatDateForDatabase($row['pengajuan_transfer_date']),
                    'gdrive_ttd_kol_accepting' => $row['gdrive_ttd_kol_accepting'],
                    'nik' => $row['nik'],
                    'price_rate' => $row['price_rate'],
                    'first_rate_card' => $row['first_rate_card'],
                    'discount' => $row['discount'],
                    'slot_final' => $row['slot_final'],
                    'tax_deduction' => $row['tax_deduction'],
                ]);
            } else {
                // Create a new talent record
                $talent = Talent::create([
                    'username' => $row['username'],
                    'talent_name' => $row['talent_name'],
                    'video_slot' => $row['video_slot'],
                    'content_type' => $row['content_type'],
                    'produk' => $row['produk'],
                    'rate_final' => $row['rate_final'],
                    'pic' => $row['pic'],
                    'bulan_running' => $row['bulan_running'],
                    'niche' => $row['niche'],
                    'followers' => $row['followers'],
                    'address' => $row['address'],
                    'phone_number' => $row['phone_number'],
                    'bank' => $row['bank'],
                    'no_rekening' => $row['no_rekening'],
                    'nama_rekening' => $row['nama_rekening'],
                    'no_npwp' => $row['no_npwp'],
                    'pengajuan_transfer_date' => $this->formatDateForDatabase($row['pengajuan_transfer_date']),
                    'gdrive_ttd_kol_accepting' => $row['gdrive_ttd_kol_accepting'],
                    'nik' => $row['nik'],
                    'price_rate' => $row['price_rate'],
                    'first_rate_card' => $row['first_rate_card'],
                    'discount' => $row['discount'],
                    'slot_final' => $row['slot_final'],
                    'tax_deduction' => $row['tax_deduction'],
                ]);
            }
        }
    }

    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255',
            'talent_name' => 'required|string|max:255',
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
            'no_rekening' => 'nullable|string|max:255',
            'nama_rekening' => 'nullable|string|max:255',
            'no_npwp' => 'nullable|string|max:255',
            'pengajuan_transfer_date' => 'nullable|date',
            'gdrive_ttd_kol_accepting' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:255',
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
