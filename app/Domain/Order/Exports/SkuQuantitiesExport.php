<?php

namespace App\Domain\Order\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SkuQuantitiesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    use Exportable;

    private $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function collection()
    {
        $skuCounts = [];
        
        DB::table('orders')
            ->select('sku')
            ->whereDate('date', $this->date)
            ->orderBy('id')
            ->chunk(1000, function($orders) use (&$skuCounts) {
                foreach ($orders as $order) {
                    $skuItems = explode(',', $order->sku);
                    
                    foreach ($skuItems as $item) {
                        $item = trim($item);
                        
                        if (preg_match('/^(\d+)\s+(.+)$/', $item, $matches)) {
                            $quantity = (int)$matches[1];
                            $skuCode = trim($matches[2]);
                        } else {
                            $quantity = 1;
                            $skuCode = trim($item);
                        }
                        
                        if (!isset($skuCounts[$skuCode])) {
                            $skuCounts[$skuCode] = 0;
                        }
                        $skuCounts[$skuCode] += $quantity;
                    }
                }
            });

        arsort($skuCounts);
        
        return collect(array_map(function($sku, $quantity) {
            return [
                'sku' => $sku,
                'quantity' => $quantity
            ];
        }, array_keys($skuCounts), $skuCounts));
    }

    public function headings(): array
    {
        return [
            'SKU',
            'Quantity'
        ];
    }
}