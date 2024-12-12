<?php

namespace App\Domain\Product\Import;

use App\Domain\Product\Models\Product;
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
class ProductImport implements ToCollection, SkipsEmptyRows, WithMapping, WithStartRow, WithUpserts, WithValidation
{
    use Importable;

    /**
     * Define the unique field for upsert operations.
     */
    public function uniqueBy(): string
    {
        return 'sku';
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
            'product' => $row[0] ?? '',
            'stock' => $row[1] ?? 0,
            'sku' => $row[2] ?? '',
            'harga_jual' => $row[3] ?? 0.00,
            'harga_markup' => $row[4] ?? 0.00,
            'harga_cogs' => $row[5] ?? 0.00,
            'harga_batas_bawah' => $row[6] ?? 0.00,
            'tenant_id' => $row[7] ?? 1,
        ];
    }

    /**
     * Handle the collection of rows from the Excel file.
     */
    public function collection(Collection $rows)
{
    foreach ($rows as $row) {
        Product::updateOrCreate(
            ['sku' => $row['sku']], // Search by SKU
            [
                'product' => $row['product'] ?? '',
                'stock' => $row['stock'] ?? 0,
                'sku' => $row['sku'] ?? '',
                'harga_jual' => $row['harga_jual'] ?? 0.00,
                'harga_markup' => $row['harga_markup'] ?? 0.00,
                'harga_cogs' => $row['harga_cogs'] ?? 0.00,
                'harga_batas_bawah' => $row['harga_batas_bawah'] ?? 0.00,
                'tenant_id' => $row['tenant_id'] ?? 1,
            ]
        );
    }
}


    /**
     * Validation rules for each row.
     */
    public function rules(): array
    {
        return [
            'product' => 'nullable|string|max:255',
            'stock' => 'nullable|integer',
            'sku' => 'nullable|string|max:255',
            'harga_jual' => 'nullable|numeric',
            'harga_markup' => 'nullable|numeric',
            'harga_cogs' => 'nullable|numeric',
            'harga_batas_bawah' => 'nullable|numeric',
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
