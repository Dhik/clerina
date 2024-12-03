<?php

namespace App\Domain\Campaign\Import;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class ContentImportKOL implements SkipsEmptyRows, ToCollection, WithMapping, WithStartRow, WithUpserts, WithValidation
{
    use Importable;

    protected array $importedData = [];

    public function batchSize(): int
    {
        return 1000;
    }

    public function uniqueBy(): string
    {
        return 'id';
    }

    protected function parseExcelDate($date): ?string
    {
        if ($date === null) {
            return null;
        }

        try {
            // Handle Excel serial dates
            if (is_numeric($date)) {
                $carbonDate = Carbon::instance(ExcelDate::excelToDateTimeObject($date));
                return $carbonDate->format('Y-m-d');
            }

            // Handle 'dd/mm/yyyy' format explicitly
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                $carbonDate = Carbon::createFromFormat('d/m/Y', $date);
                return $carbonDate->format('Y-m-d');
            }

            // Handle other string date formats
            $carbonDate = Carbon::parse($date);
            return $carbonDate->format('Y-m-d');
        } catch (\Exception $e) {
            // Log the error or handle it as needed
            return null;
        }
    }

    public function map($row): array
    {
        return [
            'username' => trim($row[0] ?? ''),
            'task_name' => $row[1] ?? null,
            'channel' => trim($row[2] ?? ''),
            'link' => trim($row[3] ?? ''),
            'rate_card' => $row[4] ?? null,
            'product' => $row[5] ?? null,
            'kode_ads' => $row[6] ?? null,
            'dealing_upload_date' => $this->parseExcelDate($row[7] ?? null),
            'nama_pic' => $row[8] ?? null,
            'kerkun' => $this->mapKerkun($row[9] ?? null),
            'posting_date' => $this->parseExcelDate($row[10] ?? '2024-11-25'),
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    private function mapKerkun(?string $value): int
    {
        if ($value === null) {
            return 0;
        }
        return strtolower($value) === 'yes' ? 1 : 0;
    }

    public function collection(Collection $rows)
    {
        $data = $rows->map(function ($row) {
            return [
                'username' => $row['username'] ?? null,
                'task_name' => $row['task_name'] ?? null,
                'channel' => trim($row['channel'] ?? ''),
                'link' => trim($row['link'] ?? ''),
                'rate_card' => $row['rate_card'] ?? null,
                'product' => $row['product'] ?? null,
                'kode_ads' => $row['kode_ads'] ?? null,
                'dealing_upload_date' => $this->parseExcelDate($row['dealing_upload_date'] ?? null),
                'nama_pic' => $row['nama_pic'] ?? null,
                'kerkun' => $this->mapKerkun($row['kerkun'] ?? null),
                'posting_date' => $this->parseExcelDate($row['posting_date'] ?? '2024-11-25'),
            ];
        })->toArray();

        $this->importedData = $data;

        return collect($data);
    }

    public function getImportedData(): array
    {
        return $this->importedData;
    }

    public function rules(): array
    {
        return [
            'username' => 'required|max:255',
            'task_name' => 'required|max:255',
            'channel' => ['required', Rule::in(CampaignContentEnum::PlatformValidation)],
            'link' => 'nullable|url',
            'rate_card' => 'nullable|numeric',
            'product' => 'required|max:255',
            'kode_ads' => 'nullable|max:255',   
            'dealing_upload_date' => 'nullable|date',
            'nama_pic' => 'nullable|max:255',
            'kerkun' => 'nullable|in:0,1',
            'posting_date' => 'nullable|date',
        ];
    }
}