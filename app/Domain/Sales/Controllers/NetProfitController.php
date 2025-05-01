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
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Services\GoogleSheetService;

class NetProfitController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        GoogleSheetService $googleSheetService
    ) {
        $this->googleSheetService = $googleSheetService;
    }
    // public function updateSpentKolAzrina()
    // {
    //     try {
    //         // Define date range: First day of current month to today
    //         $startDate = Carbon::now()->startOfMonth();
    //         $endDate = Carbon::now()->endOfDay();
            
    //         $talentPayments = TalentContent::query()
    //             ->join('talents', 'talent_content.talent_id', '=', 'talents.id')
    //             ->where('talents.tenant_id', 2)
    //             ->whereNotNull('talent_content.upload_link')
    //             ->whereBetween('talent_content.posting_date', [$startDate, $endDate])
    //             ->select('posting_date')
    //             ->selectRaw('SUM(
    //                 CASE 
    //                     WHEN talent_content.final_rate_card IS NOT NULL 
    //                     THEN talent_content.final_rate_card - (
    //                         CASE 
    //                             WHEN talents.tax_percentage IS NOT NULL AND talents.tax_percentage > 0 
    //                             THEN talent_content.final_rate_card * (talents.tax_percentage / 100)
    //                             WHEN UPPER(LEFT(talents.nama_rekening, 2)) = "PT" OR UPPER(LEFT(talents.nama_rekening, 2)) = "CV"
    //                             THEN talent_content.final_rate_card * 0.02
    //                             ELSE talent_content.final_rate_card * 0.025
    //                         END
    //                     )
    //                     ELSE (talents.rate_final / GREATEST(COALESCE(talents.slot_final, 1), 1)) - (
    //                         CASE 
    //                             WHEN talents.tax_percentage IS NOT NULL AND talents.tax_percentage > 0 
    //                             THEN (talents.rate_final / GREATEST(COALESCE(talents.slot_final, 1), 1)) * (talents.tax_percentage / 100)
    //                             WHEN UPPER(LEFT(talents.nama_rekening, 2)) = "PT" OR UPPER(LEFT(talents.nama_rekening, 2)) = "CV"
    //                             THEN (talents.rate_final / GREATEST(COALESCE(talents.slot_final, 1), 1)) * 0.02
    //                             ELSE (talents.rate_final / GREATEST(COALESCE(talents.slot_final, 1), 1)) * 0.025
    //                         END
    //                     )
    //                 END
    //             ) as talent_payment')
    //             ->groupBy('posting_date');
                
    //         // Apply same date range to NetProfit query
    //         NetProfit::query()
    //             ->where('tenant_id', 2)
    //             ->whereBetween('date', [$startDate, $endDate])
    //             ->joinSub($talentPayments, 'tp', function($join) {
    //                 $join->on('net_profits.date', '=', 'tp.posting_date');
    //             })
    //             ->update(['spent_kol' => DB::raw('tp.talent_payment')]);
                
    //         return response()->json(['success' => true]);
    //     } catch(\Exception $e) {
    //         return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    //     }
    // }
    public function updateSpentKol()
    {
        try {
            $this->googleSheetService->setSpreadsheetId('1LGAez9IydEKgLZwRnFX7_20T_hjgZ6tz3t-YXo4QBUw');
            $range = 'Import Sales!A2:R';
            $sheetData = $this->googleSheetService->getSheetData($range);
            
            $tenant_id = 1;
            $currentMonth = Carbon::now()->format('Y-m');
            
            // Create an array to store date => KOL spent mapping
            $kolSpentData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0]) || !isset($row[17])) { // 17 is index for column R
                    continue;
                }
                
                // Parse the date
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                
                // Skip if not in current month
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                $kolSpent = $this->parseCurrencyToInt($row[17]);
                $kolSpentData[$date] = $kolSpent;
            }
            // Counter for tracking updates
            $updatedCount = 0;
            
            foreach ($kolSpentData as $date => $amount) {
                // Check if record exists
                $exists = NetProfit::where('date', $date)
                        ->where('tenant_id', 1)
                        ->exists();
                
                // Only update if record exists
                if ($exists) {
                    NetProfit::where('date', $date)
                        ->where('tenant_id', 1)
                        ->update(['spent_kol' => $amount]);
                        
                    $updatedCount++;
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => "KOL spent data updated successfully. Updated {$updatedCount} records."
            ]);
        } catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function updateSpentKolAzrina()
    {
        try {
            // Set the spreadsheet ID
            $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
            
            // Define the range to get KOL spent data from column M (index 12)
            $range = 'Azrina!A2:M';
            $sheetData = $this->googleSheetService->getSheetData($range);
            
            $tenant_id = 2;
            $currentMonth = Carbon::now()->format('Y-m');
            
            // Create an array to store date => KOL spent mapping
            $kolSpentData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0]) || !isset($row[17])) { // 17 is index for column R
                    continue;
                }
                
                // Parse the date
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                
                // Skip if not in current month
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                $kolSpent = $this->parseCurrencyToInt($row[17]);
                $kolSpentData[$date] = $kolSpent;
            }
            // Counter for tracking updates
            $updatedCount = 0;
            
            foreach ($kolSpentData as $date => $amount) {
                // Check if record exists
                $exists = NetProfit::where('date', $date)
                        ->where('tenant_id', 2)
                        ->exists();
                
                // Only update if record exists
                if ($exists) {
                    NetProfit::where('date', $date)
                        ->where('tenant_id', 2)
                        ->update(['spent_kol' => $amount]);
                        
                    $updatedCount++;
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => "KOL spent data updated successfully. Updated {$updatedCount} records."
            ]);
        } catch(\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
    public function updateB2bAndCrmSales()
    {
        try {
            $range = 'Import Sales!A2:T';
            $sheetData = $this->googleSheetService->getSheetData($range);
            
            $tenant_id = 1;
            $currentMonth = Carbon::now()->format('Y-m');
            
            $salesData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0])) {
                    continue;
                }
                if (!isset($row[18]) && !isset($row[19])) {
                    continue;
                }
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                $b2bSales = isset($row[18]) ? $this->parseCurrencyToInt($row[18]) : 0;
                $crmSales = isset($row[19]) ? $this->parseCurrencyToInt($row[19]) : 0;
                
                $salesData[$date] = [
                    'b2b_sales' => $b2bSales,
                    'crm_sales' => $crmSales
                ];
            }

            $updatedCount = 0;
            $skippedCount = 0;
            
            // Update net_profits table with the sales data - update only, no create
            foreach ($salesData as $date => $data) {
                $recordExists = NetProfit::where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->exists();
                    
                if ($recordExists) {
                    NetProfit::where('date', $date)
                        ->where('tenant_id', $tenant_id)
                        ->update([
                            'b2b_sales' => $data['b2b_sales'],
                            'crm_sales' => $data['crm_sales'],
                            'updated_at' => now()
                        ]);
                        
                    $updatedCount++;
                } else {
                    // Record doesn't exist - skip instead of create
                    $skippedCount++;
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'B2B and CRM sales data updated successfully',
                'records_updated' => $updatedCount,
                'records_skipped' => $skippedCount
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateB2bAndCrmSalesAzrina()
    {
        try {
            $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
            $range = 'Azrina!A2:D';
            $sheetData = $this->googleSheetService->getSheetData($range);
            
            $tenant_id = 2;
            $currentMonth = Carbon::now()->format('Y-m');
            
            $salesData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0])) {
                    continue;
                }
                if (!isset($row[1]) && !isset($row[2])) {
                    continue;
                }
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                $b2bSales = isset($row[1]) ? $this->parseCurrencyToInt($row[1]) : 0;
                $crmSales = isset($row[2]) ? $this->parseCurrencyToInt($row[2]) : 0;
                
                $salesData[$date] = [
                    'b2b_sales' => $b2bSales,
                    'crm_sales' => $crmSales
                ];
            }

            $updatedCount = 0;
            $skippedCount = 0;
            
            foreach ($salesData as $date => $data) {
                $recordExists = NetProfit::where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->exists();
                    
                if ($recordExists) {
                    NetProfit::where('date', $date)
                        ->where('tenant_id', $tenant_id)
                        ->update([
                            'b2b_sales' => $data['b2b_sales'],
                            'crm_sales' => $data['crm_sales'],
                            'updated_at' => now()
                        ]);
                        
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'B2B and CRM sales data updated successfully',
                'records_updated' => $updatedCount,
                'records_skipped' => $skippedCount
            ]);
        } catch(\Exception $e) {
            return response()->json([
                'success' => false, 
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateHpp2()
    {
        try {
            // Change from current month to fixed date range: March 1-31, 2025
            $startDate = Carbon::createFromFormat('Y-m-d', '2025-03-01')->startOfDay();
            $endDate = Carbon::createFromFormat('Y-m-d', '2025-03-31')->endOfDay();
            $tenant_id = Auth::user()->current_tenant_id;
            
            $dates = collect();
            for($date = clone $startDate; $date->lte($endDate); $date->addDay()) {
                $dates->push($date->format('Y-m-d'));
            }

            $hppPerDate = Order::query()
                ->whereBetween('orders.success_date', [$startDate, $endDate])
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->leftJoin('products', function($join) {
                    $join->on(DB::raw("TRIM(
                        CASE 
                            WHEN orders.sku REGEXP '^[0-9]+\\s+' 
                            THEN SUBSTRING(orders.sku, LOCATE(' ', orders.sku) + 1)
                            ELSE orders.sku 
                        END
                    )"), '=', 'products.sku');
                })
                ->select(DB::raw('DATE(orders.success_date) as date'))
                ->selectRaw('COALESCE(SUM(orders.qty * products.harga_satuan), 0) as total_hpp')
                ->groupBy('success_date');

            $hppResults = $hppPerDate->get();
            $resetCount = NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->update(['hpp' => 0]);

            $updatedCount = 0;
            foreach ($hppResults as $hpp) {
                // Convert date to Y-m-d format explicitly
                $formattedDate = date('Y-m-d', strtotime($hpp->date));
                
                $updated = NetProfit::where('date', $formattedDate)
                    ->where('tenant_id', $tenant_id)
                    ->update(['hpp' => $hpp->total_hpp]);
                    
                $updatedCount += $updated;
            }
            
            return response()->json([
                'success' => true,
                'reset_count' => $resetCount,
                'updated_count' => $updatedCount
            ]);
        } catch(\Exception $e) {
            \Log::error('Update HPP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateHpp()
    {
        try {
            $startDate = now()->startOfMonth();
            $tenant_id = Auth::user()->current_tenant_id; // Using authenticated user's tenant ID instead of hardcoded value
            
            $dates = collect();
            for($date = clone $startDate; $date->lte(now()); $date->addDay()) {
                $dates->push($date->format('Y-m-d'));
            }

            $hppPerDate = Order::query()
                ->whereBetween('orders.date', [$startDate, now()])
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
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
                ->selectRaw('COALESCE(SUM(orders.qty * products.harga_satuan), 0) as total_hpp') // Simplified calculation
                ->groupBy('date');

            $hppResults = $hppPerDate->get();
            $resetCount = NetProfit::query()
                ->whereBetween('date', [$startDate, now()])
                ->where('tenant_id', $tenant_id)
                ->update(['hpp' => 0]);

            $updatedCount = 0;
            foreach ($hppResults as $hpp) {
                // Convert date to Y-m-d format explicitly
                $formattedDate = date('Y-m-d', strtotime($hpp->date));
                
                $updated = NetProfit::where('date', $formattedDate)
                    ->where('tenant_id', $tenant_id)
                    ->update(['hpp' => $hpp->total_hpp]);
                    
                $updatedCount += $updated;
            }
            
            return response()->json([
                'success' => true,
                'reset_count' => $resetCount,
                'updated_count' => $updatedCount
            ]);
        } catch(\Exception $e) {
            \Log::error('Update HPP Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateHppAzrina()
    {
        try {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->format('Y-m-d');
            $tenant_id = 2;
            
            // Get order quantities and SKUs for each day in the range
            $dailyOrders = DB::table('orders')
                ->select(DB::raw('DATE(date) as order_date'), 'sku', DB::raw('SUM(qty) as total_qty'))
                ->where('tenant_id', $tenant_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->whereNotIn('status', [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->groupBy('order_date', 'sku')
                ->get();
                
            $products = DB::table('products')
                ->select('sku', 'harga_satuan')
                ->where('tenant_id', $tenant_id)
                ->get()
                ->keyBy('sku');
            
            // Calculate HPP per day
            $dailyHpp = [];
            
            foreach ($dailyOrders as $order) {
                $orderDate = $order->order_date;
                $orderSku = $order->sku;
                $orderQty = $order->total_qty;
                
                // Extract the base SKU for cases like "2 AZ-MC50ML-001"
                $baseSku = $orderSku;
                
                if (preg_match('/^(\d+)\s+(.+)$/', $orderSku, $matches)) {
                    $baseSku = $matches[2];
                }
                
                // Get the product price
                $productPrice = 0;
                if (isset($products[$baseSku])) {
                    $productPrice = $products[$baseSku]->harga_satuan;
                }
                
                // Calculate total HPP for this order entry
                $orderHpp = $orderQty * $productPrice;
                
                // Add to daily total
                if (!isset($dailyHpp[$orderDate])) {
                    $dailyHpp[$orderDate] = 0;
                }
                $dailyHpp[$orderDate] += $orderHpp;
            }
            
            $resetCount = DB::table('net_profits')
                ->where('tenant_id', $tenant_id)
                ->whereBetween('date', [$startDate, $endDate])
                ->update(['hpp' => 0, 'updated_at' => now()]);
            
            $updatedCount = 0;
            foreach ($dailyHpp as $date => $hppValue) {
                $updated = DB::table('net_profits')
                    ->where('tenant_id', $tenant_id)
                    ->where('date', $date)
                    ->update(['hpp' => $hppValue, 'updated_at' => now()]);
                    
                $updatedCount += $updated;
            }
            
            return response()->json([
                'success' => true,
                'reset_count' => $resetCount,
                'updated_count' => $updatedCount,
                'total_hpp_days' => count($dailyHpp),
                'message' => "HPP values updated successfully for tenant_id {$tenant_id}"
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    public function updateMarketing()
    {
        try {
            // Set specific date - April 30, 2025
            $specificDate = Carbon::parse('2025-04-30');
            
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.marketing = sales.ad_spent_total, 
                    net_profits.updated_at = ?
                WHERE net_profits.date = ?
                AND sales.tenant_id = ?
            ", [now(), $specificDate->format('Y-m-d'), 1]);

            return response()->json([
                'success' => true,
                'message' => 'Marketing data updated for April 30, 2025',
                'date' => $specificDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateVisit()
    {
        try {
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.visit = sales.visit, 
                    net_profits.updated_at = ?
                WHERE MONTH(net_profits.date) = ?
                AND YEAR(net_profits.date) = ?
                AND sales.tenant_id = ?
            ", [now(), now()->month, now()->year, 1]);

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateVisitAzrina()
    {
        try {
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.visit = sales.visit, 
                    net_profits.updated_at = ?
                WHERE MONTH(net_profits.date) = ?
                AND YEAR(net_profits.date) = ?
                AND sales.tenant_id = ?
            ", [now(), now()->month, now()->year, 2]);

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateMarketingAzrina()
    {
        try {
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.marketing = sales.ad_spent_total, 
                    net_profits.updated_at = ?
                WHERE MONTH(net_profits.date) = ?
                AND YEAR(net_profits.date) = ?
                AND sales.tenant_id = ?
            ", [now(), now()->month, now()->year, 2]);

            return response()->json([
                'success' => true,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
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
        $tenantId = Auth::user()->current_tenant_id;

        // Get product details
        $productDetails = Order::query()
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
            ->where('products.tenant_id', $tenantId)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'cancelled', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ])
            ->select([
                'products.sku',
                'products.product', 
                'products.harga_satuan',
                DB::raw('SUM(orders.qty) as quantity')
            ])
            ->groupBy('products.sku', 'products.product', 'products.harga_satuan')
            ->get();

        // Get sales channel details
        $channelDetails = Order::query()
            ->join('products', function($join) {
                $join->on(DB::raw("TRIM(
                    CASE 
                        WHEN orders.sku REGEXP '^[0-9]+\\s+' 
                        THEN SUBSTRING(orders.sku, LOCATE(' ', orders.sku) + 1)
                        ELSE orders.sku 
                    END
                )"), '=', 'products.sku');
            })
            ->leftJoin('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
            ->whereDate('orders.date', $date)
            ->where('products.tenant_id', $tenantId)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'cancelled', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ])
            ->select([
                'orders.sales_channel_id',
                'sales_channels.name as channel_name',
                DB::raw('SUM(orders.qty) as quantity'),
                DB::raw('SUM(orders.qty * products.harga_satuan) as total_hpp')
            ])
            ->groupBy('orders.sales_channel_id', 'sales_channels.name')
            ->get();

        // Calculate total HPP
        $totalHpp = $channelDetails->sum('total_hpp');

        return response()->json([
            'productDetails' => $productDetails,
            'channelDetails' => $channelDetails,
            'totalHpp' => $totalHpp
        ]);
    }
    public function getSalesByChannel(Request $request)
    {
        $date = $request->date;
        $tenantId = Auth::user()->current_tenant_id;

        // Get sales by channel data
        $salesByChannel = Order::query()
            ->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
            ->whereDate('orders.date', $date)
            ->where('orders.tenant_id', $tenantId)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'cancelled', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ])
            ->select([
                'sales_channels.name as sales_channel',
                DB::raw('SUM(orders.amount) as total_sales'),
                DB::raw('COUNT(*) as order_count')
            ])
            ->groupBy('sales_channels.name')
            ->get();

        // Get net profits data for the same date and tenant
        $netProfits = DB::table('net_profits')
            ->where('date', $date)
            ->where('tenant_id', $tenantId)
            ->select(['b2b_sales', 'crm_sales'])
            ->first();

        // Create the response data including both datasets
        $responseData = [
            'salesByChannel' => $salesByChannel,
            'b2b_sales' => $netProfits ? $netProfits->b2b_sales : 0,
            'crm_sales' => $netProfits ? $netProfits->crm_sales : 0
        ];

        return response()->json($responseData);
    }
    public function importNetProfits()
    {
        try {
            $this->googleSheetService->setSpreadsheetId('1LGAez9IydEKgLZwRnFX7_20T_hjgZ6tz3t-YXo4QBUw');
            $range = 'Import Sales!A2:D';
            $sheetData = $this->googleSheetService->getSheetData($range);

            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($sheetData as $row) {
                if (empty($row[0])) continue;

                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                $affiliate = $this->parseCurrencyToInt($row[2] ?? null);
                $visit = $this->parseToInt($row[3] ?? null);

                // Check if record exists before updating
                $exists = NetProfit::where('date', $date)
                        ->where('tenant_id', 1)
                        ->exists();

                if ($exists) {
                    // Only update if record exists
                    NetProfit::where('date', $date)
                        ->where('tenant_id', 1)
                        ->update([
                            'affiliate' => $affiliate,
                            'updated_at' => now()
                        ]);
                        
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            $this->updateClosingRate();
            
            return response()->json([
                'message' => 'Data imported successfully',
                'updated' => $updatedCount,
                'skipped' => $skippedCount
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Import failed', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function importNetProfitsAzrina()
    {
        try {
            $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
            $range = 'Azrina!A2:D';
            $sheetData = $this->googleSheetService->getSheetData($range);

            $updatedCount = 0;
            $skippedCount = 0;

            foreach ($sheetData as $row) {
                if (empty($row[0])) continue;

                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                $b2bSales = $this->parseCurrencyToInt($row[2] ?? null);

                // Check if record exists before updating
                $exists = NetProfit::where('date', $date)
                        ->where('tenant_id', 2)
                        ->exists();

                if ($exists) {
                    // Only update if record exists
                    NetProfit::where('date', $date)
                        ->where('tenant_id', 2)
                        ->update([
                            'b2b_sales' => $b2bSales,
                            'updated_at' => now()
                        ]);
                        
                    $updatedCount++;
                } else {
                    $skippedCount++;
                }
            }
            
            $this->updateClosingRate();
            
            return response()->json([
                'message' => 'Data imported successfully',
                'updated' => $updatedCount,
                'skipped' => $skippedCount
            ]);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'Import failed', 
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function parseCurrencyToInt($value)
    {
        if (empty($value)) return null;
        return (int) preg_replace('/[^0-9]/', '', $value);
    }
    private function parseToInt($currency)
    {
        return (int) str_replace(['Rp', '.', ','], '', $currency);
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
                    'closing_rate' => DB::raw('ROUND((`order` / visit) * 100, 2)')
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
            $tenant_id = 1;

            // Update ROAS for records with non-zero marketing spend
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->where('marketing', '!=', 0)
                ->update([
                    'roas' => DB::raw('sales / marketing')
                ]);

            // Set ROAS to null for records with zero marketing spend
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
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
    public function updateRoasAzrina()
    {
        try {
            $tenant_id = 2;

            // Update ROAS for records with non-zero marketing spend
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->where('marketing', '!=', 0)
                ->update([
                    'roas' => DB::raw('sales / marketing')
                ]);

            // Set ROAS to null for records with zero marketing spend
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
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
    public function updateSales()
    {
        try {
            $tenant_id = 1;
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            // $startDate = Carbon::create(2025, 3, 1)->format('Y-m-d'); // March 1, 2025
            // $endDate = Carbon::create(2025, 3, 31)->format('Y-m-d');

            $netProfitDates = NetProfit::whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->pluck('date');
            
            $excludedStatuses = [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'cancelled', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ];
            
            // Counter for updated records
            $updatedCount = 0;

            // Process each net_profit record by date
            foreach ($netProfitDates as $date) {
                // Calculate total sales amount for this date and tenant
                $totalSales = DB::table('orders')
                    ->where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->whereNotIn('status', $excludedStatuses)
                    ->sum('amount');

                // Update the net_profit record for this date and tenant
                $updated = NetProfit::where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->update(['sales' => $totalSales]);
                
                if ($updated) {
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated $updatedCount net profit records with sales data",
                'date_range' => "$startDate to $endDate"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating net profit sales data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateSalesAzrina()
    {
        try {
            $tenant_id = 2;
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $netProfitDates = NetProfit::whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->pluck('date');
            
            $excludedStatuses = [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'cancelled', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ];
            
            // Counter for updated records
            $updatedCount = 0;

            // Process each net_profit record by date
            foreach ($netProfitDates as $date) {
                // Calculate total sales amount for this date and tenant
                $totalSales = DB::table('orders')
                    ->where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->whereNotIn('status', $excludedStatuses)
                    ->sum('amount');

                // Update the net_profit record for this date and tenant
                $updated = NetProfit::where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->update(['sales' => $totalSales]);
                
                if ($updated) {
                    $updatedCount++;
                }
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully updated $updatedCount net profit records with sales data",
                'date_range' => "$startDate to $endDate"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating net profit sales data',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateQty()
    {
        try {
            $tenant_id = 1;

            $dailyQty = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'cancelled', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->select('date')
                ->selectRaw('SUM(qty) as total_qty')
                ->groupBy('date');

            // Reset the quantity only for the current tenant
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->update(['qty' => 0]);

            // Update quantities only for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereMonth('net_profits.date', now()->month)
                ->whereYear('net_profits.date', now()->year)
                ->joinSub($dailyQty, 'dq', function($join) {
                    $join->on('net_profits.date', '=', 'dq.date');
                })
                ->update([
                    'qty' => DB::raw('dq.total_qty')
                ]);

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
    public function updateQtyAzrina()
    {
        try {
            $tenant_id = 2;

            $dailyQty = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'cancelled', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->select('date')
                ->selectRaw('SUM(qty) as total_qty')
                ->groupBy('date');

            // Reset the quantity only for the current tenant
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->update(['qty' => 0]);

            // Update quantities only for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereMonth('net_profits.date', now()->month)
                ->whereYear('net_profits.date', now()->year)
                ->joinSub($dailyQty, 'dq', function($join) {
                    $join->on('net_profits.date', '=', 'dq.date');
                })
                ->update([
                    'qty' => DB::raw('dq.total_qty')
                ]);

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
            $tenant_id = 1;

            $dailyOrders = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'cancelled', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->select('date')
                ->selectRaw('COUNT(DISTINCT id_order) as total_orders')
                ->groupBy('date');

            // Reset order count and packing fee only for the current tenant
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->update(['order' => 0, 'fee_packing' => 0]);
                
            // Update order count and packing fee only for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereMonth('net_profits.date', now()->month)
                ->whereYear('net_profits.date', now()->year)
                ->joinSub($dailyOrders, 'do', function($join) {
                    $join->on('net_profits.date', '=', 'do.date');
                })
                ->update([
                    'order' => DB::raw('do.total_orders'),
                    'fee_packing' => DB::raw('do.total_orders * 1000')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order count and packing fee updated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Order Count Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order count and packing fee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateOrderCountAzrina()
    {
        try {
            $tenant_id = 2;

            $dailyOrders = Order::query()
                ->whereMonth('orders.date', now()->month)
                ->whereYear('orders.date', now()->year)
                ->where('orders.tenant_id', $tenant_id)
                ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'cancelled', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->select('date')
                ->selectRaw('COUNT(DISTINCT id_order) as total_orders')
                ->groupBy('date');

            // Reset order count and packing fee only for the current tenant
            NetProfit::query()
                ->whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->where('tenant_id', $tenant_id)
                ->update(['order' => 0, 'fee_packing' => 0]);
                
            // Update order count and packing fee only for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereMonth('net_profits.date', now()->month)
                ->whereYear('net_profits.date', now()->year)
                ->joinSub($dailyOrders, 'do', function($join) {
                    $join->on('net_profits.date', '=', 'do.date');
                })
                ->update([
                    'order' => DB::raw('do.total_orders'),
                    'fee_packing' => DB::raw('do.total_orders * 1000')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order count and packing fee updated successfully.'
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Order Count Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order count and packing fee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getCurrentMonthCorrelation(Request $request)
    {
        try {
            // Validate and get variable from request
            $variable = $request->input('variable', 'marketing');
            $columnName = in_array($variable, [
                'marketing', 'spent_kol', 'affiliate'
            ]) ? $variable : 'marketing';

            // Build query
            $query = NetProfit::query()
                ->whereNotNull('sales')
                ->whereNotNull($columnName)
                ->where($columnName, '>', 0);

            // Handle date filtering
            if ($request->filled('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            } else {
                $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
            }

            $data = $query->select([
                'date',
                'sales',
                $columnName,
                DB::raw("ROUND(sales/$columnName, 2) as ratio")
            ])->get();

            $n = $data->count();
            if ($n < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough data points for correlation analysis',
                ], 400);
            }

            $sumX = $data->sum($columnName);
            $sumY = $data->sum('sales');
            $sumXY = $data->sum(function($item) use ($columnName) {
                return $item->$columnName * $item->sales;
            });
            $sumX2 = $data->sum(function($item) use ($columnName) {
                return $item->$columnName * $item->$columnName;
            });
            $sumY2 = $data->sum(function($item) {
                return $item->sales * $item->sales;
            });

            // Check for division by zero conditions
            $denominatorX = ($n * $sumX2 - $sumX * $sumX);
            $denominatorY = ($n * $sumY2 - $sumY * $sumY);

            if ($denominatorX <= 0 || $denominatorY <= 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot calculate correlation - insufficient variance in the data',
                ], 400);
            }

            $correlation = $n * $sumXY - $sumX * $sumY;
            $correlation /= sqrt($denominatorX * $denominatorY);

            // Additional validation for correlation result
            if (is_nan($correlation) || is_infinite($correlation)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid correlation result - please check your data',
                ], 400);
            }

            // Calculate regression line
            $xMean = $sumX / $n;
            $yMean = $sumY / $n;
            $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
            $intercept = $yMean - $slope * $xMean;

            // Define column labels
            $columnLabels = [
                'marketing' => 'Marketing Spend',
                'spent_kol' => 'KOL Spending',
                'affiliate' => 'Affiliate'
            ];

            // Prepare date range for title
            $titleDate = $request->filled('filterDates') 
                ? " (" . $request->filterDates . ")"
                : " (" . now()->format('F Y') . ")";

            // Prepare data for Plotly
            $plotlyData = [
                [
                    'type' => 'scatter',
                    'mode' => 'markers',
                    'name' => 'Sales vs ' . $columnLabels[$columnName],
                    'x' => $data->pluck($columnName)->values(),
                    'y' => $data->pluck('sales')->values(),
                    'text' => $data->map(function($item) use ($columnName, $columnLabels) {
                        return 'Date: ' . $item->date . '<br>' .
                            'Sales: Rp ' . number_format($item->sales, 0, ',', '.') . '<br>' .
                            $columnLabels[$columnName] . ': Rp ' . number_format($item->$columnName, 0, ',', '.') . '<br>' .
                            'Ratio: ' . $item->ratio;
                    }),
                    'hoverinfo' => 'text',
                    'marker' => [
                        'size' => 10,
                        'color' => '#8884d8',
                        'opacity' => 0.7
                    ]
                ],
                [
                    'type' => 'scatter',
                    'mode' => 'lines',
                    'name' => 'Trend Line',
                    'x' => [$data->min($columnName), $data->max($columnName)],
                    'y' => [
                        $slope * $data->min($columnName) + $intercept,
                        $slope * $data->max($columnName) + $intercept
                    ],
                    'line' => [
                        'color' => '#ff7300',
                        'width' => 2
                    ]
                ]
            ];

            // Define layout
            $layout = [
                'title' => 'Sales vs ' . $columnLabels[$columnName] . ' Correlation' . $titleDate,
                'xaxis' => [
                    'title' => $columnLabels[$columnName] . ' (Rp)',
                    'tickformat' => ',.0f',
                    'hoverformat' => ',.0f'
                ],
                'yaxis' => [
                    'title' => 'Sales (Rp)',
                    'tickformat' => ',.0f',
                    'hoverformat' => ',.0f'
                ],
                'showlegend' => true,
                'annotations' => [
                    [
                        'x' => 0.05,
                        'y' => 0.95,
                        'xref' => 'paper',
                        'yref' => 'paper',
                        'text' => sprintf(
                            'Correlation (r): %.4f<br>R²: %.4f',
                            $correlation,
                            $correlation * $correlation
                        ),
                        'showarrow' => false,
                        'bgcolor' => '#ffffff',
                        'bordercolor' => '#000000',
                        'borderwidth' => 1
                    ]
                ]
            ];

            // Return JSON response
            return response()->json([
                'data' => $plotlyData,
                'layout' => $layout,
                'statistics' => [
                    'correlation' => round($correlation, 4),
                    'r_squared' => round($correlation * $correlation, 4),
                    'slope' => round($slope, 4),
                    'intercept' => round($intercept, 4),
                    'data_points' => $n
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Correlation Analysis Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze correlation.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getAdSpentDetail(Request $request)
    {
        $date = $request->input('date');
        $results = [];
        
        // Get social media ad spending
        $socialMediaData = AdSpentSocialMedia::where('date', $date)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->where('amount', '>', 0) // Only get amounts greater than 0
            ->with('socialMedia')
            ->get();
        
        foreach ($socialMediaData as $item) {
            $results[] = [
                'name' => $item->socialMedia->name ?? 'Unknown',
                'amount' => $item->amount
            ];
        }
        
        // Get marketplace ad spending
        $marketplaceData = AdSpentMarketPlace::where('date', $date)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->where('amount', '>', 0) // Only get amounts greater than 0
            ->with('salesChannel')
            ->get();
        
        foreach ($marketplaceData as $item) {
            $results[] = [
                'name' => $item->salesChannel->name ?? 'Unknown',
                'amount' => $item->amount
            ];
        }
        
        return response()->json(['data' => $results]);
    }
    public function exportLK()
    {
        $newSpreadsheetId = '1Ukssd8FRbGA6Pa_Rsn3FJ2SP_W2CS4rkIhh3o5yw1gQ';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 1;
        
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
        // Get all sales data aggregated by sales channel
        $salesData = DB::table('laporan_keuangan as lk')
            ->select(
                'sc.id as channel_id',
                'sc.name as channel_name',
                DB::raw('SUM(lk.gross_revenue) as total_gross_revenue'),
                DB::raw('SUM(lk.sales) as total_sales'),
                DB::raw('SUM(lk.hpp) as total_hpp'),
                DB::raw('SUM(lk.fee_admin) as total_fee_admin')
            )
            ->leftJoin('sales_channels as sc', 'lk.sales_channel_id', '=', 'sc.id')
            ->where('lk.tenant_id', '=', $currentTenantId)
            ->whereMonth('lk.date', $now->month)
            ->whereYear('lk.date', $now->year)
            ->groupBy('sc.id', 'sc.name')
            ->get();
        
        // Get additional data from net_profits and sales tables if needed
        $additionalData = DB::table('net_profits as np')
            ->select(
                DB::raw('SUM(np.marketing) as total_marketing'),
                DB::raw('SUM(np.spent_kol) as total_kol'),
                DB::raw('SUM(np.affiliate) as total_affiliate'),
                DB::raw('SUM(np.operasional) as total_operasional'),
                DB::raw('SUM(s.ad_spent_social_media) as total_ad_spent_social'),
                DB::raw('SUM(s.ad_spent_market_place) as total_ad_spent_marketplace')
            )
            ->leftJoin('sales as s', function($join) use ($currentTenantId) {
                $join->on('np.date', '=', 's.date')
                    ->where('s.tenant_id', '=', $currentTenantId);
            })
            ->where('np.tenant_id', '=', $currentTenantId)
            ->whereMonth('np.date', $now->month)
            ->whereYear('np.date', $now->year)
            ->first();
        
        // Prepare data for Google Sheets in the format shown in the image
        $data = [];
        
        // Title
        $data[] = ['LABA RUGI KONSOLIDASI BRAND CLEORA'];
        $data[] = []; // Empty row
        
        // Gross Revenue section
        $data[] = ['Gross Revenue', ''];
        
        // Format channel names as shown in the image
        $shopeeChannels = $salesData->filter(function($item) {
            return str_contains(strtolower($item->channel_name), 'shopee');
        });
        
        // Add Shopee Mall
        $shopeeMall = $shopeeChannels->where('channel_name', 'Shopee')->first();
        $data[] = ['', 'Shopee Mall', $shopeeMall ? (int)$shopeeMall->total_gross_revenue : 0];
        
        // Add Shopee Toko Bandung
        $shopeeBandung = $shopeeChannels->where('channel_name', 'Shopee 2 - Bandung')->first();
        $data[] = ['', 'Shopee Toko Bandung', $shopeeBandung ? (int)$shopeeBandung->total_gross_revenue : 0];
        
        // Add Shopee Toko Jakarta
        $shopeeJakarta = $shopeeChannels->where('channel_name', 'Shopee 3 - Jakarta')->first();
        $data[] = ['', 'Shopee Toko Jakarta', $shopeeJakarta ? (int)$shopeeJakarta->total_gross_revenue : 0];
        
        // Add Tiktok Mall
        $tiktok = $salesData->where('channel_name', 'Tiktok Shop')->first();
        $data[] = ['', 'Tiktok Mall', $tiktok ? (int)$tiktok->total_gross_revenue : 0];
        
        // Add Lazada
        $lazada = $salesData->where('channel_name', 'Lazada')->first();
        $data[] = ['', 'Lazada', $lazada ? (int)$lazada->total_gross_revenue : 0];
        
        // Add Tokopedia
        $tokopedia = $salesData->where('channel_name', 'Tokopedia')->first();
        $data[] = ['', 'Tokopedia', $tokopedia ? (int)$tokopedia->total_gross_revenue : 0];
        
        // Add B2B
        $b2b = $salesData->where('channel_name', 'B2B')->first();
        $data[] = ['', 'B2B', $b2b ? (int)$b2b->total_gross_revenue : 0];
        
        // Add CRM
        $crm = $salesData->where('channel_name', 'CRM')->first();
        $data[] = ['', 'Others Sales Chanel (CRM)', $crm ? (int)$crm->total_gross_revenue : 0];
        
        // Calculate total gross revenue
        $totalGrossRevenue = $salesData->sum('total_gross_revenue');
        $data[] = ['', 'Total Gross Revenue', (int)$totalGrossRevenue];
        
        // Fee Admin section (replacing Net Revenue)
        $data[] = ['Fee Admin', ''];
        
        // Add fee admin for each channel
        $data[] = ['', 'Fee Admin Shopee', $shopeeMall ? (int)$shopeeMall->total_fee_admin : 0];
        $data[] = ['', 'Fee Admin Shopee Bandung', $shopeeBandung ? (int)$shopeeBandung->total_fee_admin : 0];
        $data[] = ['', 'Fee Admin Shopee Jakarta', $shopeeJakarta ? (int)$shopeeJakarta->total_fee_admin : 0];
        $data[] = ['', 'Fee Admin Tiktok', $tiktok ? (int)$tiktok->total_fee_admin : 0];
        $data[] = ['', 'Fee Admin Lazada', $lazada ? (int)$lazada->total_fee_admin : 0];
        $data[] = ['', 'Fee Admin Tokopedia', $tokopedia ? (int)$tokopedia->total_fee_admin : 0];
        
        // Add B2B fee admin (if applicable)
        $data[] = ['', 'Fee Admin B2B', $b2b ? (int)$b2b->total_fee_admin : 0];
        
        // Add CRM fee admin (if applicable)
        $data[] = ['', 'Fee Admin CRM', $crm ? (int)$crm->total_fee_admin : 0];
        
        // Calculate total fee admin
        $totalFeeAdmin = $salesData->sum('total_fee_admin');
        $data[] = ['', 'Total Fee Admin', (int)$totalFeeAdmin];
        
        // Calculate total deductions - no longer needed with Fee Admin approach
        // $totalDeductions = ($shopeeMall ? $shopeeMall->total_sales * $potentialMPRate : 0) +
        //                  ($shopeeBandung ? $shopeeBandung->total_sales * $potentialMPRate : 0) +
        //                  ($shopeeJakarta ? $shopeeJakarta->total_sales * $potentialMPRate : 0) +
        //                  ($tiktok ? $tiktok->total_sales * $potentialMPRate : 0) +
        //                  ($lazada ? $lazada->total_sales * $potentialMPRate : 0) +
        //                  ($tokopedia ? $tokopedia->total_sales * $potentialMPRate : 0) +
        //                  ($crm ? $crm->total_sales * 0.05 : 0) +
        //                  ($b2b ? $b2b->total_sales * 0.1 : 0) +
        //                  ($crm ? $crm->total_sales * 0.08 : 0);
        
        // Calculate net revenue - now it's gross revenue minus fee admin
        $netRevenue = $totalGrossRevenue - $totalFeeAdmin;
        $data[] = ['Net Revenue', (int)$netRevenue];
        
        // PPN section (assuming 11% tax)
        $ppnRate = 0.11;
        $ppn = $netRevenue * $ppnRate;
        $data[] = ['PPN', (int)$ppn];
        
        // COGS (Cost of Goods Sold) section
        $data[] = ['COGS', ''];
        
        // Add HPP for each channel
        $data[] = ['', 'HPP Shopee Mall', $shopeeMall ? (int)$shopeeMall->total_hpp : 0];
        $data[] = ['', 'HPP Shopee Bandung', $shopeeBandung ? (int)$shopeeBandung->total_hpp : 0];
        $data[] = ['', 'HPP Shopee Jakarta', $shopeeJakarta ? (int)$shopeeJakarta->total_hpp : 0];
        $data[] = ['', 'HPP Tiktok', $tiktok ? (int)$tiktok->total_hpp : 0];
        $data[] = ['', 'HPP Lazada', $lazada ? (int)$lazada->total_hpp : 0];
        $data[] = ['', 'HPP Tokopedia', $tokopedia ? (int)$tokopedia->total_hpp : 0];
        
        // Calculate total COGS
        $totalCOGS = $salesData->sum('total_hpp');
        $data[] = ['', 'Total COGS', (int)$totalCOGS];
        
        // Calculate Gross Profit
        $grossProfit = $netRevenue - $ppn - $totalCOGS;
        $data[] = ['Gross Profit', (int)$grossProfit];
        
        // Add marketing expenses and operational costs from additional data if needed
        if ($additionalData) {
            $data[] = ['Marketing Expenses', ''];
            $data[] = ['', 'Social Media Ads', (int)($additionalData->total_ad_spent_social ?? 0)];
            $data[] = ['', 'Marketplace Ads', (int)($additionalData->total_ad_spent_marketplace ?? 0)];
            $data[] = ['', 'KOL Spending', (int)($additionalData->total_kol ?? 0)];
            $data[] = ['', 'Affiliate Marketing', (int)($additionalData->total_affiliate ?? 0)];
            
            $totalMarketing = ($additionalData->total_ad_spent_social ?? 0) + 
                            ($additionalData->total_ad_spent_marketplace ?? 0) + 
                            ($additionalData->total_kol ?? 0) + 
                            ($additionalData->total_affiliate ?? 0);
            
            $data[] = ['', 'Total Marketing Expenses', (int)$totalMarketing];
            
            // Operational expenses
            $data[] = ['Operational Expenses', (int)($additionalData->total_operasional ?? 0)];
            
            // Calculate Net Profit
            $netProfit = $grossProfit - $totalMarketing - ($additionalData->total_operasional ?? 0);
            $data[] = ['Net Profit', (int)$netProfit];
        }
        
        $sheetName = 'Laporan Keuangan';
        
        // Export to Google Sheets
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'),
            'count' => count($data) - 1
        ]);
    }
    public function exportCurrentMonthData()
    {
        $newSpreadsheetId = '1Ukssd8FRbGA6Pa_Rsn3FJ2SP_W2CS4rkIhh3o5yw1gQ';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 1;
        
        $now = Carbon::now();
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
        // Use the same query structure as your getNetProfit method
        $baseQuery = DB::table('net_profits as np')
            ->select(
                'np.*', 
                's.ad_spent_social_media',
                's.ad_spent_market_place'
            )
            ->leftJoin('sales as s', function($join) use ($currentTenantId) {
                $join->on('np.date', '=', 's.date')
                    ->where('s.tenant_id', '=', $currentTenantId);
            })
            ->where('np.tenant_id', '=', $currentTenantId)
            ->whereMonth('np.date', $now->month)
            ->whereYear('np.date', $now->year)
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        // Prepare data for Google Sheets
        $data = [];
        
        // Add headers with all the columns
        $data[] = [
            'Date', 
            'Net Profit',
            'Sales', 
            'B2B Sales', 
            'CRM Sales', 
            'Total Sales',
            'Net Sales',
            'Marketing', 
            'KOL Spending', 
            'Affiliate',
            'Total Marketing',
            'Operasional', 
            'HPP', 
            'Fee Packing',
            'Admin Fee',
            'PPN',
            'ROAS',
            'ROMI',
            'Visits',
            'Quantity',
            'Orders',
            'Closing Rate',
            'Ad Spent (Social)',
            'Ad Spent (Marketplace)'
        ];
        
        // Helper function to ensure number format
        $ensureNumber = function($value) {
            // Cast to float to ensure it's a number
            // This will handle both string and numeric inputs
            return (float)$value;
        };
        
        // Add rows
        foreach ($records as $row) {
            // Calculate all the derived fields (similar to your DataTables method)
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.713;
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.16;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.713) - 
            ($ensureNumber($row->marketing ?? 0) * 1.05) - 
            $ensureNumber($row->spent_kol ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                $ensureNumber($row->b2b_sales ?? 0),
                $ensureNumber($row->crm_sales ?? 0),
                $totalSales,
                $ensureNumber(($row->sales * 0.713) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->sales * 0.01) ?? 0),
                $ensureNumber(($row->sales * 0.167) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100, // Convert percentage to decimal
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        $sheetName = 'SalesReport';
        
        // Export to Google Sheets - using a wider range to accommodate all columns
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'),
            'count' => count($data) - 1 // Subtract 1 for header row
        ]);
    }
    public function exportProductReport()
    {
        $newSpreadsheetId = '1iM61qRxDgjSj6fVnhXrYRA-pY2RtH8XTJqSfvWVYoGs';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = Auth::user()->current_tenant_id;
        
        $now = Carbon::now();
        $currentMonth = $now->month;
        $currentYear = $now->year;
        
        // First period: 1st to 15th
        $firstPeriodStart = Carbon::createFromDate($currentYear, $currentMonth, 1)->format('Y-m-d');
        $firstPeriodEnd = Carbon::createFromDate($currentYear, $currentMonth, 15)->format('Y-m-d');
        
        // Second period: 16th to end of month
        $secondPeriodStart = Carbon::createFromDate($currentYear, $currentMonth, 16)->format('Y-m-d');
        $secondPeriodEnd = Carbon::createFromDate($currentYear, $currentMonth, 1)->endOfMonth()->format('Y-m-d');
        
        // Function to get data for a specific period
        $getDataForPeriod = function($startDate, $endDate, $periodLabel) use ($currentTenantId) {
            $orders = Order::select(
                    'orders.sku',
                    'products.product as product_name', // Added product name
                    DB::raw('SUM(orders.qty) as total_qty'),
                    DB::raw('COUNT(orders.id) as total_orders'),
                    DB::raw('AVG(orders.amount) as average_order_value'),
                    DB::raw('SUM(orders.amount) as gmv'),
                    DB::raw('COUNT(DISTINCT orders.customer_phone_number) as unique_customers')
                )
                ->leftJoin('products', function($join) use ($currentTenantId) {
                    $join->on('orders.sku', '=', 'products.sku')
                        ->where('products.tenant_id', '=', $currentTenantId);
                })
                ->where('orders.tenant_id', $currentTenantId)
                ->whereBetween('orders.date', [$startDate, $endDate])
                ->groupBy('orders.sku', 'products.product')
                ->orderBy('orders.sku')
                ->get();
            
            $periodData = [];
            foreach ($orders as $row) {
                $periodData[] = [
                    $periodLabel,
                    $row->sku,
                    $row->product_name ?? 'Unknown Product', // Fallback if product not found
                    (int)$row->total_qty,
                    (int)$row->total_orders,
                    (float)$row->average_order_value,
                    (float)$row->gmv,
                    (int)$row->unique_customers
                ];
            }
            
            return $periodData;
        };
        
        // Prepare data for Google Sheets
        $data = [];
        
        // Add headers with new Product Name column
        $data[] = [
            'Period', 
            'SKU',
            'Product Name',
            'Qty', 
            'Orders', 
            'AOV', 
            'GMV',
            'Unique Customers'
        ];
        
        // Get data for first period (1-15)
        $firstPeriodData = $getDataForPeriod($firstPeriodStart, $firstPeriodEnd, '1-15 ' . $now->format('M Y'));
        
        // Get data for second period (16-end)
        $lastDay = $now->copy()->endOfMonth()->day;
        $secondPeriodData = $getDataForPeriod($secondPeriodStart, $secondPeriodEnd, "16-{$lastDay} " . $now->format('M Y'));
        
        // Combine data
        $data = array_merge($data, $firstPeriodData, $secondPeriodData);
        
        $sheetName = 'Product Report';
        
        // Export to Google Sheets (updated range to H to accommodate the new column)
        $this->googleSheetService->clearRange("$sheetName!A1:H1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Product report data exported successfully to Google Sheets',
            'month' => $now->format('F Y'),
            'periods' => [
                '1-15 ' . $now->format('M Y'),
                "16-{$lastDay} " . $now->format('M Y')
            ],
            'count' => count($data) - 1 // Subtract 1 for header row
        ]);
    }
    // public function exportProductReport()
    // {
    //     $newSpreadsheetId = '1iM61qRxDgjSj6fVnhXrYRA-pY2RtH8XTJqSfvWVYoGs';

    //     if ($newSpreadsheetId) {
    //         $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
    //     }
    //     $currentTenantId = Auth::user()->current_tenant_id;
        
    //     $now = Carbon::now();
    //     $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
    //     $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
    //     // Query to get orders for the current month
    //     $orders = DB::table('orders')
    //         ->select(
    //             DB::raw('DATE(date) as order_date'),
    //             'sku',
    //             DB::raw('SUM(qty) as total_qty'),
    //             DB::raw('COUNT(id) as total_orders'),
    //             DB::raw('AVG(amount) as average_order_value'),
    //             DB::raw('SUM(amount) as gmv'),
    //             DB::raw('COUNT(DISTINCT customer_phone_number) as unique_customers')
    //         )
    //         ->where('tenant_id', '=', $currentTenantId)
    //         ->whereMonth('date', $now->month)
    //         ->whereYear('date', $now->year)
    //         ->groupBy('order_date', 'sku')
    //         ->orderBy('order_date')
    //         ->orderBy('sku')
    //         ->get();
        
    //     // Prepare data for Google Sheets
    //     $data = [];
        
    //     // Add headers
    //     $data[] = [
    //         'Date', 
    //         'SKU', 
    //         'Qty', 
    //         'Orders', 
    //         'AOV', 
    //         'GMV',
    //         'Unique Customers'
    //     ];
        
    //     // Add rows
    //     foreach ($orders as $row) {
    //         $data[] = [
    //             $row->order_date,
    //             $row->sku,
    //             (int)$row->total_qty,
    //             (int)$row->total_orders,
    //             (float)$row->average_order_value,
    //             (float)$row->gmv,
    //             (int)$row->unique_customers
    //         ];
    //     }
        
    //     $sheetName = 'Product Report';
        
    //     // Export to Google Sheets
    //     $this->googleSheetService->clearRange("$sheetName!A1:G1000");
    //     $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
    //     return response()->json([
    //         'success' => true, 
    //         'message' => 'Product report data exported successfully to Google Sheets',
    //         'month' => $now->format('F Y'),
    //         'count' => count($data) - 1 // Subtract 1 for header row
    //     ]);
    // }
}