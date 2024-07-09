<?php

namespace App\Domain\Order\Import;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class PreprocessShopeeData implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        // Filter out rows with null 'nomor_invoice'
        return $rows->filter(function ($row) {
            return !is_null($row['nomor_invoice']);
        });
    }
}
