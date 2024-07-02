<?php

namespace App\Domain\Marketing\Import;

use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class MarketingImport implements SkipsEmptyRows, ToModel, WithMapping, WithStartRow, WithValidation
{
    use Importable;

    protected array $importedData = [];

    public function __construct(protected int $tenantId)
    {
    }

    public function map($row): array
    {
        return [
            'date' => Date::excelToDateTimeObject($row[0])->format('d/m/Y'),
            'type' => $row[1],
            'marketing_category_id' => $row[2],
            'marketing_sub_category_id' => $row[3],
            'amount' => $row[4],
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function model(array $row): Model|array|Marketing|null
    {
        $category = MarketingCategory::where('name', $row['marketing_category_id'])->firstOrFail()->id;

        $subCategory = null;
        if (! empty($row['marketing_sub_category_id'])) {
            $subCategory = MarketingSubCategory::where('name', $row['marketing_sub_category_id'])->first()->id;
        }

        $marketing = Marketing::updateOrCreate([
            'date' => Carbon::createFromFormat('d/m/Y', $row['date'])->format('Y-m-d'),
            'type' => $row['type'],
            'marketing_category_id' => $category,
            'marketing_sub_category_id' => $subCategory,
            'tenant_id' => $this->tenantId,
        ], [
            'amount' => $row['amount'],
        ]);

        $this->importedData[] = $marketing;

        return $marketing;
    }

    public function getImportedData(): array
    {
        return $this->importedData;
    }

    public function rules(): array
    {
        return [
            'date' => 'required|date_format:d/m/Y',
            'type' => 'required',
            'marketing_category_id' => 'required|exists:marketing_categories,name',
            'marketing_sub_category_id' => 'required_if:type,marketing',
            'amount' => 'required|numeric|integer',
        ];
    }
}
