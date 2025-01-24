<?php

namespace App\Domain\Sales\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Domain\Sales\Models\NetProfit;
use Carbon\Carbon;
use App\Domain\Order\Models\Order;
use App\Http\Controllers\Controller;
use App\Domain\Talent\Models\TalentContent;

class NetProfitController extends Controller
{
    public function updateSpentKol()
    {
        try {
            $talentPayments = TalentContent::query()
                ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
                ->where('talents.tenant_id', 1)
                ->whereNotNull('talent_content.upload_link')
                ->whereMonth('talent_content.posting_date', date('m'))
                ->whereYear('talent_content.posting_date', date('Y'))
                ->select('posting_date')
                ->selectRaw('SUM(CASE 
                    WHEN final_rate_card IS NOT NULL 
                    THEN final_rate_card 
                    ELSE talents.rate_final / GREATEST(COALESCE(talents.slot_final, 1), 1)
                    END) as talent_payment')
                ->groupBy('posting_date');

            NetProfit::query()
                ->joinSub($talentPayments, 'tp', function($join) {
                    $join->on('net_profits.date', '=', 'tp.posting_date');
                })
                ->update(['spent_kol' => DB::raw('tp.talent_payment')]);
                
            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
    public function updateHpp()
    {
        try {
            $startDate = now()->startOfMonth();
            $dates = collect();
            for($date = $startDate; $date->lte(now()); $date->addDay()) {
                $dates->push($date->format('Y-m-d'));
            }
            $hppPerDate = Order::query()
                ->rightJoin(DB::raw("(SELECT UNNEST(ARRAY[" . $dates->map(fn($d) => "'$d'")->join(',') . "]) as date) as dr"), 
                    DB::raw('DATE(orders.date)'), '=', 'dr.date')
                ->leftJoin('products', function($join) {
                    $join->on(DB::raw('TRIM(
                        CASE 
                            WHEN orders.sku REGEXP \'^[0-9]+\\s+\' 
                            THEN SUBSTRING(orders.sku, LOCATE(\' \', orders.sku) + 1)
                            ELSE orders.sku 
                        END
                    )'), '=', 'products.sku');
                })
                ->whereNotIn('orders.status', ['pending', 'cancelled', 'request_cancel', 'request_return'])
                ->select('dr.date')
                ->selectRaw('COALESCE(SUM(
                    CASE 
                        WHEN orders.sku REGEXP \'^[0-9]+\\s+\'
                        THEN products.harga_satuan * CAST(SUBSTRING_INDEX(orders.sku, \' \', 1) AS UNSIGNED)
                        ELSE products.harga_satuan
                    END
                ), 0) as total_hpp')
                ->groupBy('dr.date');

            NetProfit::query()
                ->joinSub($hppPerDate, 'hpp', function($join) {
                    $join->on('net_profits.date', '=', 'hpp.date');
                })
                ->update(['hpp' => DB::raw('hpp.total_hpp')]);
            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            return response()->json(['success' => false], 500);
        }
    }
    public function importSalesData()
    {
        $startDate = '2025-01-01';
        
        return NetProfit::query()->insertUsing(
            ['date', 'sales', 'marketing'],
            Sale::query()
                ->select('date', 'turnover', 'ad_spent_total')
                ->where('date', '>=', $startDate)
                ->where('tenant_id', 1)
        );
    }
}