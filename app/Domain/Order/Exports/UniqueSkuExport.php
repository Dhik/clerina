<?php

namespace App\Domain\Order\Exports;

use App\Domain\Order\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;

class UniqueSkuExport implements FromCollection
{
    /**
     * Return a collection of unique SKUs.
     *
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        // Get unique SKU values from orders table
        return Order::select('sku')->distinct()->get();
    }
}
