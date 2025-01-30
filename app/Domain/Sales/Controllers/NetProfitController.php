<?php

namespace App\Domain\Sales\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Domain\Sales\Models\NetProfit;
use Carbon\Carbon;
use Auth;
use App\Domain\Order\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Sales\Services\GoogleSheetService;

class NetProfitController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        GoogleSheetService $googleSheetService
    ) {
        $this->googleSheetService = $googleSheetService;
    }
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
            for($date = clone $startDate; $date->lte(now()); $date->addDay()) {
                $dates->push($date->format('Y-m-d'));
            }

            $hppPerDate = Order::query()
                ->whereBetween('orders.date', [$startDate, now()])
                ->where('orders.tenant_id', Auth::user()->current_tenant_id)
                ->whereNotIn('orders.status', ['pending', 'cancelled', 'request_cancel', 'request_return'])
                ->leftJoin('products', function($join) {
                    $join->on(DB::raw("TRIM(
                        CASE 
                            WHEN orders.sku REGEXP '^[0-9]+\\s+' 
                            THEN SUBSTRING(orders.sku, LOCATE(' ', orders.sku) + 1)
                            ELSE orders.sku 
                        END
                    )"), '=', 'products.sku');
                })
                ->select(DB::raw('DATE(orders.date) as date'))
                ->selectRaw('COALESCE(SUM(
                    CASE 
                        WHEN orders.sku REGEXP "^[0-9]+\\s+"
                        THEN products.harga_satuan * CAST(SUBSTRING_INDEX(orders.sku, " ", 1) AS UNSIGNED)
                        ELSE products.harga_satuan
                    END
                ), 0) as total_hpp')
                ->groupBy('date');

            NetProfit::query()
                ->whereBetween('date', [$startDate, now()])
                ->update(['hpp' => 0]);

            NetProfit::query()
                ->joinSub($hppPerDate, 'hpp', function($join) {
                    $join->on('net_profits.date', '=', 'hpp.date');
                })
                ->update(['hpp' => DB::raw('hpp.total_hpp')]);

            return response()->json(['success' => true]);
        } catch(\Exception $e) {
            \Log::error('Update HPP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateMarketing()
    {
        try {
            NetProfit::query()
                ->join('sales', 'net_profits.date', '=', 'sales.date')
                ->whereMonth('net_profits.date', now()->month)
                ->whereYear('net_profits.date', now()->year)
                ->where('sales.tenant_id', Auth::user()->current_tenant_id)
                ->update([
                    'net_profits.marketing' => DB::raw('sales.ad_spent_total')
                ]);

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
            ], 500);
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
    public function getHppByDate(Request $request)
    {
        $date = $request->date;

        $hppDetails = Order::query()
            ->join('products', function($join) {
                $join->on(DB::raw("TRIM(
                    CASE 
                        WHEN orders.sku REGEXP '^[0-9]+\\s+' 
                        THEN SUBSTRING(orders.sku, LOCATE(' ', orders.sku) + 1)
                        ELSE orders.sku 
                    END
                )"), '=', 'products.sku');
            })
            ->whereDate('orders.date', $date)
            ->where('products.tenant_id', Auth::user()->current_tenant_id)
            ->whereNotIn('orders.status', ['pending', 'cancelled', 'request_cancel', 'request_return'])
            ->select([
                'products.sku',
                'products.product', 
                'products.harga_satuan',
                DB::raw('COUNT(*) as order_count'),
                DB::raw('SUM(CASE 
                    WHEN orders.sku REGEXP "^[0-9]+\\s+"
                    THEN CAST(SUBSTRING_INDEX(orders.sku, " ", 1) AS UNSIGNED)
                    ELSE 1 
                END) as quantity')
            ])
            ->groupBy('products.sku', 'products.product', 'products.harga_satuan')
            ->get();

        return response()->json($hppDetails);
    }
    public function importNetProfits()
    {
        try {
            $range = 'Import Sales!A2:D';
            $sheetData = $this->googleSheetService->getSheetData($range);

            foreach ($sheetData as $row) {
                if (empty($row[0])) continue;

                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                $sales = $this->parseCurrencyToInt($row[1] ?? null);
                $affiliate = $this->parseCurrencyToInt($row[2] ?? null);
                $visit = empty($row[3]) ? null : (int)$row[3];

                NetProfit::updateOrCreate(
                    ['date' => $date],
                    [
                        'sales' => $sales,
                        'affiliate' => $affiliate,
                        'visit' => $visit
                    ]
                );
            }
            $this->updateClosingRate();

            return response()->json(['message' => 'Data imported successfully']);
        } catch (Exception $e) {
            return response()->json(['message' => 'Import failed', 'error' => $e->getMessage()], 500);
        }
    }
    private function parseCurrencyToInt($value)
    {
        if (empty($value)) return null;
        return (int) preg_replace('/[^0-9]/', '', $value);
    }
    public function updateClosingRate()
    {
        try {
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->whereNotNull('visit')
                ->whereNotNull('order')
                ->where('visit', '>', 0)
                ->update([
                    'closing_rate' => DB::raw('ROUND((visit / `order`) * 100, 2)')
                ]);

            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where(function($query) {
                    $query->whereNull('visit')
                        ->orWhereNull('order')
                        ->orWhere('visit', 0)
                        ->orWhere('order', 0);
                })
                ->update(['closing_rate' => 0]);

            return response()->json([
                'success' => true,
                'message' => 'Closing rate updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Closing Rate Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update closing rate.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateRoas()
    {
        try {
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('marketing', '!=', 0)
                ->update([
                    'roas' => DB::raw('sales / marketing')
                ]);
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('marketing', 0)
                ->update([
                    'roas' => null 
                ]);

            return response()->json([
                'success' => true,
                'message' => 'ROAS updated successfully.'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update ROAS Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ROAS.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateQty()
    {
        try {
            $dailyQty = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', Auth::user()->current_tenant_id)
                ->whereNotIn('orders.status', ['pending', 'cancelled', 'request_cancel', 'request_return'])
                ->select('date')
                ->selectRaw('SUM(qty) as total_qty')
                ->groupBy('date');

            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->update(['qty' => 0]);

            NetProfit::query()
                ->joinSub($dailyQty, 'dq', function($join) {
                    $join->on('net_profits.date', '=', 'dq.date');
                })
                ->update(['qty' => DB::raw('dq.total_qty')]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Qty Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateOrderCount()
    {
        try {
            $dailyOrders = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', Auth::user()->current_tenant_id)
                ->select('date')
                ->selectRaw('COUNT(DISTINCT id_order) as total_orders')
                ->groupBy('date');

            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->update(['order' => 0]);
                
            NetProfit::query()
                ->joinSub($dailyOrders, 'do', function($join) {
                    $join->on('net_profits.date', '=', 'do.date');
                })
                ->update(['order' => DB::raw('do.total_orders')]);

            return response()->json([
                'success' => true,
                'message' => 'Order count updated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Order Count Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order count.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}