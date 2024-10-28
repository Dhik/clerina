<?php

namespace App\Domain\Campaign\Import;

use App\Domain\Campaign\Enums\CampaignContentEnum;
use App\Domain\Customer\BLL\Customer\CustomerBLL;
use App\Domain\Order\Models\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class ContentImport implements SkipsEmptyRows, ToCollection, WithMapping, WithStartRow, WithUpserts, WithValidation
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

    public function map($row): array
    {
        return [
            'username' => $row[0],
            'task_name' => $row[1],
            'channel' => trim($row[2]),
            'link' => trim($row[3]),
            'rate_card' => $row[4],
            'product' => $row[5],
            'kode_ads' => $row[6]
        ];
    }

    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        $data = [];
        foreach ($rows as $row) {
            $data[] = [
                'username' => $row['username'],
                'task_name' => $row['task_name'],
                'channel' => trim($row['channel']),
                'link' => trim($row['link']),
                'rate_card' => $row['rate_card'],
                'product' => $row['product'],
                'kode_ads' => $row['kode_ads']
            ];
        }
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
            'rate_card' => 'numeric',
            'product' => 'required|max:255',
            'kode_ads' => 'nullable|max:255'
        ];
    }
}
