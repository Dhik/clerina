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
            $startDate = Carbon::now()->subDays(40)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            // Create an array to store date => KOL spent mapping
            $kolSpentData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0]) || !isset($row[17])) { // 17 is index for column R
                    continue;
                }
                
                // Parse the date
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                $parsedDate = Carbon::parse($date);
                
                // Skip if not within the past 40 days
                if ($parsedDate->lt($startDate) || $parsedDate->gt($endDate)) {
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
                'message' => "KOL spent data updated successfully for the past 40 days. Updated {$updatedCount} records."
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
            $startDate = Carbon::now()->subDays(40)->startOfDay();
            $endDate = Carbon::now()->endOfDay();
            
            // Create an array to store date => KOL spent mapping
            $kolSpentData = [];
            
            foreach ($sheetData as $row) {
                if (empty($row) || empty($row[0]) || !isset($row[12])) { // 12 is index for column M
                    continue;
                }
                
                // Parse the date
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                $parsedDate = Carbon::parse($date);
                
                // Skip if not within the past 40 days
                if ($parsedDate->lt($startDate) || $parsedDate->gt($endDate)) {
                    continue;
                }
                
                $kolSpent = $this->parseCurrencyToInt($row[12]);
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
                'message' => "KOL spent data updated successfully for the past 40 days. Updated {$updatedCount} records."
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
            $twoDaysAgo = now()->subDays(2)->startOfDay();
            $yesterday = now()->subDay()->endOfDay();
            $tenant_id = Auth::user()->current_tenant_id; // Using authenticated user's tenant ID
            
            $hppPerDate = Order::query()
                ->whereBetween('orders.date', [$twoDaysAgo, $yesterday])
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
                ->selectRaw('COALESCE(SUM(orders.qty * products.harga_satuan), 0) as total_hpp')
                ->groupBy('date');
            
            $hppResults = $hppPerDate->get();
            
            // Reset HPP for the last 2 days
            $resetCount = NetProfit::query()
                ->whereBetween('date', [$twoDaysAgo->format('Y-m-d'), $yesterday->format('Y-m-d')])
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
                'date_range' => $twoDaysAgo->format('Y-m-d') . ' to ' . $yesterday->format('Y-m-d'),
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.marketing = sales.ad_spent_total, 
                    net_profits.updated_at = ?
                WHERE net_profits.date BETWEEN ? AND ?
                AND sales.tenant_id = ?
            ", [now(), $startDate, $endDate, 1]);

            return response()->json([
                'success' => true,
                'message' => "Marketing data updated successfully for the past 40 days.",
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.visit = sales.visit, 
                    net_profits.updated_at = ?
                WHERE net_profits.date BETWEEN ? AND ?
                AND sales.tenant_id = ?
            ", [now(), $startDate, $endDate, 1]);

            return response()->json([
                'success' => true,
                'message' => "Visit data updated successfully for the past 40 days.",
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.visit = sales.visit, 
                    net_profits.updated_at = ?
                WHERE net_profits.date BETWEEN ? AND ?
                AND sales.tenant_id = ?
            ", [now(), $startDate, $endDate, 2]);

            return response()->json([
                'success' => true,
                'message' => "Visit data updated successfully for Azrina tenant for the past 40 days.",
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.marketing = sales.ad_spent_total, 
                    net_profits.updated_at = ?
                WHERE net_profits.date BETWEEN ? AND ?
                AND sales.tenant_id = ?
            ", [now(), $startDate, $endDate, 2]);

            return response()->json([
                'success' => true,
                'message' => "Marketing data updated successfully for Azrina tenant for the past 40 days.",
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            // Update ROAS for records with non-zero marketing spend for the past 40 days
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->where('marketing', '!=', 0)
                ->update([
                    'roas' => DB::raw('sales / marketing')
                ]);

            // Set ROAS to null for records with zero marketing spend for the past 40 days
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->where('marketing', 0)
                ->update([
                    'roas' => null 
                ]);

            return response()->json([
                'success' => true,
                'message' => 'ROAS updated successfully for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            // Update ROAS for records with non-zero marketing spend for the past 40 days
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->where('marketing', '!=', 0)
                ->update([
                    'roas' => DB::raw('sales / marketing')
                ]);

            // Set ROAS to null for records with zero marketing spend for the past 40 days
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->where('marketing', 0)
                ->update([
                    'roas' => null 
                ]);

            return response()->json([
                'success' => true,
                'message' => 'ROAS updated successfully for Azrina tenant for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Update ROAS Azrina Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update ROAS for Azrina tenant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateSales()
    {
        try {
            $tenant_id = 1;
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
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
                'message' => "Successfully updated $updatedCount net profit records with sales data for the past 40 days",
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
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
                'message' => "Successfully updated $updatedCount net profit records with sales data for Azrina tenant for the past 40 days",
                'date_range' => "$startDate to $endDate"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating net profit sales data for Azrina tenant',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateQty()
    {
        try {
            $tenant_id = 1;
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $dailyQty = Order::query()
                ->whereBetween('orders.date', [$startDate, $endDate])
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

            // Reset the quantity for the past 40 days for the current tenant
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->update(['qty' => 0]);

            // Update quantities for the past 40 days for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereBetween('net_profits.date', [$startDate, $endDate])
                ->joinSub($dailyQty, 'dq', function($join) {
                    $join->on('net_profits.date', '=', 'dq.date');
                })
                ->update([
                    'qty' => DB::raw('dq.total_qty')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $dailyQty = Order::query()
                ->whereBetween('orders.date', [$startDate, $endDate])
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

            // Reset the quantity for the past 40 days for Azrina tenant
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->update(['qty' => 0]);

            // Update quantities for the past 40 days for Azrina tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereBetween('net_profits.date', [$startDate, $endDate])
                ->joinSub($dailyQty, 'dq', function($join) {
                    $join->on('net_profits.date', '=', 'dq.date');
                })
                ->update([
                    'qty' => DB::raw('dq.total_qty')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Quantity updated successfully for Azrina tenant for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Qty Azrina Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update quantity for Azrina tenant.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateOrderCount()
    {
        try {
            $tenant_id = 1;
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $dailyOrders = Order::query()
                ->whereBetween('orders.date', [$startDate, $endDate])
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

            // Reset order count and packing fee for the past 40 days for the current tenant
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->update(['order' => 0, 'fee_packing' => 0]);
                
            // Update order count and packing fee for the past 40 days for the current tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereBetween('net_profits.date', [$startDate, $endDate])
                ->joinSub($dailyOrders, 'do', function($join) {
                    $join->on('net_profits.date', '=', 'do.date');
                })
                ->update([
                    'order' => DB::raw('do.total_orders'),
                    'fee_packing' => DB::raw('do.total_orders * 2000')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order count and packing fee updated successfully for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
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
            $startDate = Carbon::now()->subDays(40)->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $dailyOrders = Order::query()
                ->whereBetween('orders.date', [$startDate, $endDate])
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

            // Reset order count and packing fee for the past 40 days for Azrina tenant
            NetProfit::query()
                ->whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', $tenant_id)
                ->update(['order' => 0, 'fee_packing' => 0]);
                
            // Update order count and packing fee for the past 40 days for Azrina tenant
            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereBetween('net_profits.date', [$startDate, $endDate])
                ->joinSub($dailyOrders, 'do', function($join) {
                    $join->on('net_profits.date', '=', 'do.date');
                })
                ->update([
                    'order' => DB::raw('do.total_orders'),
                    'fee_packing' => DB::raw('do.total_orders * 2000')
                ]);

            return response()->json([
                'success' => true,
                'message' => 'Order count and packing fee updated successfully for Azrina tenant for the past 40 days.',
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Update Order Count Azrina Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update order count and packing fee for Azrina tenant.',
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
                
            // Exclude twin dates (where day equals month)
            $query->whereRaw('DAY(date) != MONTH(date)');

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
    public function getDetailCorrelation(Request $request)
    {
        try {
            // Get SKU and Platform from request
            $sku = $request->input('sku', 'all');
            $platform = $request->input('platform', 'all');
            
            // Debug: Log the received parameters
            \Log::info('Debug - Received parameters:', [
                'sku' => $sku,
                'platform' => $platform,
                'raw_platform' => $request->input('platform')
            ]);
            
            // Build query
            $query = \DB::table('relation_ads_sales')
                ->whereNotNull('sales')
                ->whereNotNull('marketing')
                ->where('marketing', '>', 0)
                ->where('tenant_id', 1);

            // Apply SKU filter if not 'all'
            if ($sku !== 'all') {
                $query->where('sku', $sku);
            }

            // Apply Platform filter if not 'all'
            if ($platform !== 'all') {
                $query->where('platform', $platform);
            }

            // Handle date filtering
            if ($request->filled('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                    $query->whereBetween('date', [$startDate, $endDate]);
                }
            } else {
                // Default to May 2025 since that's where your data is
                $query->whereBetween('date', ['2025-05-01', '2025-05-19']);
            }

            // Debug: Log the query and count
            $debugQuery = clone $query;
            $totalCount = $debugQuery->count();
            \Log::info('Debug - Query count:', ['total_records' => $totalCount]);
            
            // Debug: Log sample of available platforms
            $availablePlatforms = \DB::table('relation_ads_sales')
                ->whereBetween('date', ['2025-05-01', '2025-05-19'])
                ->select('platform')
                ->distinct()
                ->pluck('platform');
            \Log::info('Debug - Available platforms:', $availablePlatforms->toArray());

            $data = $query->select([
                'date',
                'sku',
                'platform',
                'sales',
                'marketing',
                \DB::raw("ROUND(sales/marketing, 2) as ratio")
            ])->get();

            $n = $data->count();
            
            // Debug: Log filtered data count
            \Log::info('Debug - Filtered data count:', ['filtered_records' => $n]);
            
            if ($n < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not enough data points for correlation analysis',
                    'debug' => [
                        'total_records' => $totalCount,
                        'filtered_records' => $n,
                        'sku_filter' => $sku,
                        'platform_filter' => $platform,
                        'available_platforms' => $availablePlatforms->toArray()
                    ]
                ], 400);
            }

            // Rest of your existing correlation calculation code...
            $sumX = $data->sum('marketing');
            $sumY = $data->sum('sales');
            $sumXY = $data->sum(function($item) {
                return $item->marketing * $item->sales;
            });
            $sumX2 = $data->sum(function($item) {
                return $item->marketing * $item->marketing;
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

            // Map SKU codes to product names
            $skuLabels = [
                'CLE-RS-047' => 'Red Saviour',
                'CLE-JB30-001' => 'Jelly Booster',
                'CL-GS' => 'Glowsmooth',
                'CLE-XFO-008' => '3 Minutes',
                'CLE-CLNDLA-025' => 'Calendula',
                'CLE-NEG-071' => 'Natural Exfo',
                'CL-TNR' => 'Pore Glow',
                'CL-8XHL' => '8X Hyalu',
                '-' => 'Lain-lain'
            ];

            // Define title based on selected SKU and Platform
            $skuTitle = ($sku === 'all') ? 'All Products' : $skuLabels[$sku] . ' (' . $sku . ')';
            $platformTitle = ($platform === 'all') ? 'All Platforms' : $platform;
            
            // Prepare date range for title
            $titleDate = $request->filled('filterDates') 
                ? " (" . $request->filterDates . ")"
                : " (May 1-19, 2025)";

            // Define colors for different SKUs and platforms
            $skuColors = [
                'CLE-RS-047' => '#FF6384',
                'CLE-JB30-001' => '#36A2EB',
                'CL-GS' => '#FFCE56',
                'CLE-XFO-008' => '#4BC0C0',
                'CLE-CLNDLA-025' => '#9966FF',
                'CLE-NEG-071' => '#FF9F40',
                'CL-TNR' => '#C9CBCF',
                'CL-8XHL' => '#7BC8A4',
                '-' => '#BDBDBD'
            ];

            $platformColors = [
                'Meta Ads' => '#1877F2',
                'Shopee Ads' => '#EE4D2D',
                'Meta and Shopee Ads' => '#8B4A90'
            ];

            // Prepare data for Plotly based on filter combinations
            if ($sku === 'all' && $platform === 'all') {
                // Group data by both SKU and Platform for all view
                $traces = [];
                foreach ($skuLabels as $skuCode => $skuName) {
                    $platforms = $data->where('sku', $skuCode)->pluck('platform')->unique();
                    foreach ($platforms as $plt) {
                        $filteredData = $data->where('sku', $skuCode)->where('platform', $plt);
                        if ($filteredData->count() > 0) {
                            $traces[] = [
                                'type' => 'scatter',
                                'mode' => 'markers',
                                'name' => $skuName . ' (' . $plt . ')',
                                'x' => $filteredData->pluck('marketing')->values(),
                                'y' => $filteredData->pluck('sales')->values(),
                                'text' => $filteredData->map(function($item) use ($skuLabels) {
                                    return 'Date: ' . $item->date . '<br>' .
                                        'Product: ' . $skuLabels[$item->sku] . '<br>' .
                                        'Platform: ' . $item->platform . '<br>' .
                                        'Sales: Rp ' . number_format($item->sales, 0, ',', '.') . '<br>' .
                                        'Marketing: Rp ' . number_format($item->marketing, 0, ',', '.') . '<br>' .
                                        'Ratio: ' . $item->ratio;
                                }),
                                'hoverinfo' => 'text',
                                'marker' => [
                                    'size' => 10,
                                    'color' => $platformColors[$plt] ?? $skuColors[$skuCode],
                                    'opacity' => 0.7
                                ]
                            ];
                        }
                    }
                }
                $plotlyData = $traces;
            } else if ($sku === 'all' && $platform !== 'all') {
                // Group by SKU for single platform
                $traces = [];
                foreach ($skuLabels as $skuCode => $skuName) {
                    $filteredData = $data->where('sku', $skuCode);
                    if ($filteredData->count() > 0) {
                        $traces[] = [
                            'type' => 'scatter',
                            'mode' => 'markers',
                            'name' => $skuName,
                            'x' => $filteredData->pluck('marketing')->values(),
                            'y' => $filteredData->pluck('sales')->values(),
                            'text' => $filteredData->map(function($item) use ($skuLabels) {
                                return 'Date: ' . $item->date . '<br>' .
                                    'Product: ' . $skuLabels[$item->sku] . '<br>' .
                                    'Platform: ' . $item->platform . '<br>' .
                                    'Sales: Rp ' . number_format($item->sales, 0, ',', '.') . '<br>' .
                                    'Marketing: Rp ' . number_format($item->marketing, 0, ',', '.') . '<br>' .
                                    'Ratio: ' . $item->ratio;
                            }),
                            'hoverinfo' => 'text',
                            'marker' => [
                                'size' => 10,
                                'color' => $skuColors[$skuCode],
                                'opacity' => 0.7
                            ]
                        ];
                    }
                }
                $plotlyData = $traces;
            } else if ($sku !== 'all' && $platform === 'all') {
                // Group by platform for single SKU
                $traces = [];
                $platforms = $data->pluck('platform')->unique();
                foreach ($platforms as $plt) {
                    $filteredData = $data->where('platform', $plt);
                    if ($filteredData->count() > 0) {
                        $traces[] = [
                            'type' => 'scatter',
                            'mode' => 'markers',
                            'name' => $plt,
                            'x' => $filteredData->pluck('marketing')->values(),
                            'y' => $filteredData->pluck('sales')->values(),
                            'text' => $filteredData->map(function($item) use ($skuLabels) {
                                return 'Date: ' . $item->date . '<br>' .
                                    'Product: ' . $skuLabels[$item->sku] . '<br>' .
                                    'Platform: ' . $item->platform . '<br>' .
                                    'Sales: Rp ' . number_format($item->sales, 0, ',', '.') . '<br>' .
                                    'Marketing: Rp ' . number_format($item->marketing, 0, ',', '.') . '<br>' .
                                    'Ratio: ' . $item->ratio;
                            }),
                            'hoverinfo' => 'text',
                            'marker' => [
                                'size' => 10,
                                'color' => $platformColors[$plt] ?? '#999999',
                                'opacity' => 0.7
                            ]
                        ];
                    }
                }
                $plotlyData = $traces;
            } else {
                // Single product and platform view with trend line
                $color = $platformColors[$platform] ?? $skuColors[$sku] ?? '#999999';
                $plotlyData = [
                    [
                        'type' => 'scatter',
                        'mode' => 'markers',
                        'name' => 'Sales vs Marketing',
                        'x' => $data->pluck('marketing')->values(),
                        'y' => $data->pluck('sales')->values(),
                        'text' => $data->map(function($item) use ($skuLabels) {
                            return 'Date: ' . $item->date . '<br>' .
                                'Platform: ' . $item->platform . '<br>' .
                                'Sales: Rp ' . number_format($item->sales, 0, ',', '.') . '<br>' .
                                'Marketing: Rp ' . number_format($item->marketing, 0, ',', '.') . '<br>' .
                                'Ratio: ' . $item->ratio;
                        }),
                        'hoverinfo' => 'text',
                        'marker' => [
                            'size' => 10,
                            'color' => $color,
                            'opacity' => 0.7
                        ]
                    ],
                    [
                        'type' => 'scatter',
                        'mode' => 'lines',
                        'name' => 'Trend Line',
                        'x' => [$data->min('marketing'), $data->max('marketing')],
                        'y' => [
                            $slope * $data->min('marketing') + $intercept,
                            $slope * $data->max('marketing') + $intercept
                        ],
                        'line' => [
                            'color' => '#ff7300',
                            'width' => 2
                        ]
                    ]
                ];
            }

            // Define layout
            $layout = [
                'title' => 'Sales vs Marketing: ' . $skuTitle . ' - ' . $platformTitle . $titleDate,
                'xaxis' => [
                    'title' => 'Marketing Spend (Rp)',
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
            \Log::error('Detail Correlation Analysis Error: ' . $e->getMessage());
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
    public function exportCurrentMonthDataAzrina()
    {
        $newSpreadsheetId = '1Ukssd8FRbGA6Pa_Rsn3FJ2SP_W2CS4rkIhh3o5yw1gQ';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 2;
        
        $now = Carbon::create(2025, 5, 1);
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
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
            ->whereMonth('np.date', $now->month)  // Will be April (4)
            ->whereYear('np.date', $now->year)    // Will be 2025
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        $data = [];
        $data[] = [
            'Date', 
            'Net Profit',
            'Total Sales', 
            'Estimasi Cancel (6%)',
            'Estimasi Retur (2%)',
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
        
        $ensureNumber = function($value) {
            return (float)$value;
        };
        
        foreach ($records as $row) {
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.725 - ($row->fee_packing ?? 0);
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.165;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.725) - 
            ($ensureNumber($row->marketing ?? 0)) - 
            $ensureNumber($row->spent_kol ?? 0) -
            $ensureNumber($row->fee_packing ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                // $ensureNumber($row->b2b_sales ?? 0),
                // $ensureNumber($row->crm_sales ?? 0),
                // $totalSales,
                $ensureNumber(($row->sales * 0.06) ?? 0),
                $ensureNumber(($row->sales * 0.02) ?? 0),
                $ensureNumber(($row->sales * 0.725) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->fee_packing) ?? 0),
                $ensureNumber(($row->sales * 0.165) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100,
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        // Update sheet name to April 2025
        $sheetName = 'SalesReport Azrina';
        
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'), // Will output April 2025
            'count' => count($data) - 1
        ]);
    }
    public function exportLastMonthDataAzrina()
    {
        $newSpreadsheetId = '1Ukssd8FRbGA6Pa_Rsn3FJ2SP_W2CS4rkIhh3o5yw1gQ';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 2;
        
        $now = Carbon::create(2025, 5, 1);
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
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
            ->whereMonth('np.date', $now->month)  // Will be April (4)
            ->whereYear('np.date', $now->year)    // Will be 2025
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        $data = [];
        $data[] = [
            'Date', 
            'Net Profit',
            'Total Sales', 
            'Estimasi Cancel (6%)',
            'Estimasi Retur (2%)',
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
        
        $ensureNumber = function($value) {
            return (float)$value;
        };
        
        foreach ($records as $row) {
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.725 - ($row->fee_packing ?? 0);
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.165;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.725) - 
            ($ensureNumber($row->marketing ?? 0)) - 
            $ensureNumber($row->spent_kol ?? 0) -
            $ensureNumber($row->fee_packing ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                // $ensureNumber($row->b2b_sales ?? 0),
                // $ensureNumber($row->crm_sales ?? 0),
                // $totalSales,
                $ensureNumber(($row->sales * 0.06) ?? 0),
                $ensureNumber(($row->sales * 0.02) ?? 0),
                $ensureNumber(($row->sales * 0.725) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->fee_packing) ?? 0),
                $ensureNumber(($row->sales * 0.165) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100,
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        // Update sheet name to April 2025
        $sheetName = 'Daily Count Azrina May 2025';
        
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'), // Will output April 2025
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
        
        $now = Carbon::create(2025, 6, 1);
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
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
            ->whereMonth('np.date', $now->month)  // Will be April (4)
            ->whereYear('np.date', $now->year)    // Will be 2025
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        $data = [];
        $data[] = [
            'Date', 
            'Net Profit',
            'Total Sales', 
            'Estimasi Cancel (6%)',
            'Estimasi Retur (2%)',
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
        
        $ensureNumber = function($value) {
            return (float)$value;
        };
        
        foreach ($records as $row) {
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.725 - ($row->fee_packing ?? 0);
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.165;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.725) - 
            ($ensureNumber($row->marketing ?? 0)) - 
            $ensureNumber($row->spent_kol ?? 0) -
            $ensureNumber($row->fee_packing ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                // $ensureNumber($row->b2b_sales ?? 0),
                // $ensureNumber($row->crm_sales ?? 0),
                // $totalSales,
                $ensureNumber(($row->sales * 0.06) ?? 0),
                $ensureNumber(($row->sales * 0.02) ?? 0),
                $ensureNumber(($row->sales * 0.725) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->fee_packing) ?? 0),
                $ensureNumber(($row->sales * 0.165) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100,
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        // Update sheet name to April 2025
        $sheetName = 'SalesReport Cleora';
        
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'), // Will output April 2025
            'count' => count($data) - 1
        ]);
    }
    public function exportAdsMeta()
    {
        $newSpreadsheetId = '17Sls8V-UH5gWobRCqxdeI8IySx7a7Nf7HGs3cDUI2F8';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 1;
        
        $now = Carbon::create(2025, 5, 1);
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
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
            ->whereMonth('np.date', $now->month)  // Will be April (4)
            ->whereYear('np.date', $now->year)    // Will be 2025
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        $data = [];
        $data[] = [
            'Date', 
            'Net Profit',
            'Total Sales', 
            'Estimasi Cancel (6%)',
            'Estimasi Retur (2%)',
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
        
        $ensureNumber = function($value) {
            return (float)$value;
        };
        
        foreach ($records as $row) {
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.725 - ($row->fee_packing ?? 0);
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.165;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.725) - 
            ($ensureNumber($row->marketing ?? 0)) - 
            $ensureNumber($row->spent_kol ?? 0) -
            $ensureNumber($row->fee_packing ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                // $ensureNumber($row->b2b_sales ?? 0),
                // $ensureNumber($row->crm_sales ?? 0),
                // $totalSales,
                $ensureNumber(($row->sales * 0.06) ?? 0),
                $ensureNumber(($row->sales * 0.02) ?? 0),
                $ensureNumber(($row->sales * 0.725) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->fee_packing) ?? 0),
                $ensureNumber(($row->sales * 0.165) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100,
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        // Update sheet name to April 2025
        $sheetName = 'Ads Meta';
        
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'), // Will output April 2025
            'count' => count($data) - 1
        ]);
    }
    public function exportHPPLastMonth()
    {
        $newSpreadsheetId = '1SSdZTupgguBggAJy0QkvOx6hqtVs8A8Rb2tcimAIRa8';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        $currentTenantId = 1;
        
        $now = Carbon::create(2025, 4, 1);
        $startDate = $now->copy()->startOfMonth()->format('Y-m-d');
        $endDate = $now->copy()->endOfMonth()->format('Y-m-d');
        
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
            ->whereMonth('np.date', $now->month)  // Will be April (4)
            ->whereYear('np.date', $now->year)    // Will be 2025
            ->orderBy('np.date');
        
        $records = $baseQuery->get();
        
        $data = [];
        $data[] = [
            'Date', 
            'Net Profit',
            'Total Sales', 
            'Estimasi Cancel (6%)',
            'Estimasi Retur (2%)',
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
        
        $ensureNumber = function($value) {
            return (float)$value;
        };
        
        foreach ($records as $row) {
            $totalSales = $ensureNumber($row->sales ?? 0) + $ensureNumber($row->b2b_sales ?? 0) + $ensureNumber($row->crm_sales ?? 0);
            $netSales = $totalSales * 0.725 - ($row->fee_packing ?? 0);
            $totalMarketingSpend = $ensureNumber($row->marketing ?? 0) + $ensureNumber($row->spent_kol ?? 0) + $ensureNumber($row->affiliate ?? 0);
            $romi = ($totalMarketingSpend == 0) ? 0 : ($ensureNumber($row->sales ?? 0) / $totalMarketingSpend);
            $feeAds = $ensureNumber($row->marketing ?? 0) * 0.02;
            $estimasiFeeAdmin = $ensureNumber($row->sales ?? 0) * 0.165;
            $ppn = $ensureNumber($row->sales ?? 0) * 0.03;
            $netProfit = ($ensureNumber($row->sales ?? 0) * 0.725) - 
            ($ensureNumber($row->marketing ?? 0)) - 
            $ensureNumber($row->spent_kol ?? 0) -
            $ensureNumber($row->fee_packing ?? 0) - 
            $ensureNumber($row->affiliate ?? 0) - 
            $ensureNumber($row->operasional ?? 0) - 
            ($ensureNumber($row->hpp ?? 0) * 0.94);
            
            $data[] = [
                Carbon::parse($row->date)->format('Y-m-d'),
                $netProfit,
                $ensureNumber($row->sales ?? 0),
                // $ensureNumber($row->b2b_sales ?? 0),
                // $ensureNumber($row->crm_sales ?? 0),
                // $totalSales,
                $ensureNumber(($row->sales * 0.06) ?? 0),
                $ensureNumber(($row->sales * 0.02) ?? 0),
                $ensureNumber(($row->sales * 0.725) ?? 0),
                $ensureNumber($row->marketing ?? 0),
                $ensureNumber($row->spent_kol ?? 0),
                $ensureNumber($row->affiliate ?? 0),
                $totalMarketingSpend,
                $ensureNumber($row->operasional ?? 0),
                $ensureNumber(($row->hpp * 0.94) ?? 0),
                $ensureNumber(($row->fee_packing) ?? 0),
                $ensureNumber(($row->sales * 0.165) ?? 0),
                $ensureNumber(($row->sales * 0.03) ?? 0),
                $ensureNumber($row->roas ?? 0),
                $romi,
                (int)($row->visit ?? 0),
                (int)($row->qty ?? 0),
                (int)($row->order ?? 0),
                $ensureNumber($row->closing_rate ?? 0) / 100,
                $ensureNumber($row->ad_spent_social_media ?? 0),
                $ensureNumber($row->ad_spent_market_place ?? 0)
            ];
        }
        
        // Update sheet name to April 2025
        $sheetName = 'Sheet1';
        
        $this->googleSheetService->clearRange("$sheetName!A1:Z1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        return response()->json([
            'success' => true, 
            'message' => 'Current month data exported successfully to Google Sheets',
            'month' => $now->format('F Y'),
            'count' => count($data) - 1
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
    public function getSalesOptimization(Request $request)
    {
        try {
            $sku = $request->input('sku', 'all');
            $tenantId = 1;
            
            $endDate = now();
            $startDate = now()->subDays(60);
            
            if ($request->filled('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->endOfDay();
                }
            }
            
            // Build base query
            $query = \DB::table('relation_ads_sales')
                ->whereNotNull('sales')
                ->whereNotNull('marketing')
                ->where('marketing', '>', 0)
                ->where('tenant_id', $tenantId)
                ->whereBetween('date', [$startDate, $endDate]);
                
            // Apply SKU filter if not 'all'
            if ($sku !== 'all') {
                $query->where('sku', $sku);
            }
            
            // Get historical data
            $historicalData = $query->select([
                'date',
                'sku',
                'platform',
                'sales',
                'marketing'
            ])->orderBy('date')->get();
            
            if ($historicalData->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No data available for the selected criteria'
                ]);
            }
            
            // Calculate logistic regression data
            $logisticData = $this->calculateLogisticRegression($historicalData, $endDate);
            
            // Calculate KPI cards
            $kpi = $this->calculateTodayKPI($logisticData);
            
            // Prepare SKU breakdown
            $skuBreakdown = $this->calculateSKUBreakdown($historicalData, $logisticData);
            
            return response()->json([
                'success' => true,
                'logistic_data' => $logisticData,
                'kpi' => $kpi,
                'sku_breakdown' => $skuBreakdown
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Sales Optimization Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to analyze sales optimization data.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    private function calculateLogisticRegression($data, $endDate)
    {
        // Group data by date and platform with ROAS calculation
        $dailyData = $data->groupBy('date')->map(function ($dayItems) {
            $platforms = $dayItems->groupBy('platform');
            
            $metaItems = $platforms->get('Meta Ads', collect());
            $shopeeItems = $platforms->get('Shopee Ads', collect());
            
            $metaSpent = $metaItems->sum('marketing');
            $metaSales = $metaItems->sum('sales');
            $metaRoas = $metaSpent > 0 ? $metaSales / $metaSpent : 0;
            
            $shopeeSpent = $shopeeItems->sum('marketing');
            $shopeeSales = $shopeeItems->sum('sales');
            $shopeeRoas = $shopeeSpent > 0 ? $shopeeSales / $shopeeSpent : 0;
            
            return [
                'meta_spent' => $metaSpent,
                'meta_sales' => $metaSales,
                'meta_roas' => $metaRoas,
                'shopee_spent' => $shopeeSpent,
                'shopee_sales' => $shopeeSales,
                'shopee_roas' => $shopeeRoas,
                'total_spent' => $metaSpent + $shopeeSpent,
                'total_sales' => $metaSales + $shopeeSales
            ];
        })->sortKeys();
        
        $dates = $dailyData->keys()->toArray();
        $metaData = $dailyData->pluck('meta_spent')->toArray();
        $shopeeData = $dailyData->pluck('shopee_spent')->toArray();
        
        // Calculate optimal spending based on ROAS performance
        $optimalSpending = $this->calculateOptimalSpending($dailyData);
        
        // Generate forecast dates
        $forecastDates = [];
        $currentDate = Carbon::parse(end($dates))->addDay();
        for ($i = 0; $i < 3; $i++) {
            $forecastDates[] = $currentDate->format('Y-m-d');
            $currentDate->addDay();
        }
        
        // Generate optimized trend lines
        $metaTrend = $this->generateOptimizedTrend($metaData, $optimalSpending['meta_optimal'], count($dates) + 3);
        $shopeeTrend = $this->generateOptimizedTrend($shopeeData, $optimalSpending['shopee_optimal'], count($dates) + 3);
        
        // Create optimized forecasts
        $metaForecast = array_fill(0, 3, $optimalSpending['meta_optimal']);
        $shopeeForecast = array_fill(0, 3, $optimalSpending['shopee_optimal']);
        
        // Create full date range
        $allDates = array_merge($dates, $forecastDates);
        
        return [
            'dates' => $allDates,
            'historical_dates' => $dates,
            'forecast_dates' => $forecastDates,
            'meta_historical' => $metaData,
            'shopee_historical' => $shopeeData,
            'meta_regression' => $metaTrend,
            'shopee_regression' => $shopeeTrend,
            'meta_forecast' => $metaForecast,
            'shopee_forecast' => $shopeeForecast,
            'optimization_data' => $optimalSpending
        ];
    }

    private function generateSmoothTrend($data, $totalPoints)
    {
        $n = count($data);
        if ($n < 3) {
            return array_fill(0, $totalPoints, array_sum($data) / $n);
        }
        
        // Use exponential smoothing for trend line
        $alpha = 0.3; // Smoothing factor
        $smoothed = [$data[0]];
        
        for ($i = 1; $i < $n; $i++) {
            $smoothed[$i] = $alpha * $data[$i] + (1 - $alpha) * $smoothed[$i - 1];
        }
        
        // Extend trend for forecast period
        $lastValue = end($smoothed);
        $secondLastValue = $smoothed[$n - 2];
        $trendSlope = $lastValue - $secondLastValue;
        
        // Generate full trend line
        $trend = $smoothed;
        for ($i = $n; $i < $totalPoints; $i++) {
            $trend[$i] = $trend[$i - 1] + $trendSlope * 0.5; // Dampen the trend
        }
        
        return $trend;
    }

    private function calculateOptimalSpending($dailyData)
    {
        // Calculate performance metrics for each platform
        $metaPerformance = $this->analyzeplatformPerformance($dailyData, 'meta');
        $shopeePerformance = $this->analyzeplatformPerformance($dailyData, 'shopee');
        
        // Find optimal spending levels based on ROAS
        $metaOptimal = $this->findOptimalSpendingLevel($metaPerformance);
        $shopeeOptimal = $this->findOptimalSpendingLevel($shopeePerformance);
        
        // Calculate total budget and allocation
        $totalOptimal = $metaOptimal + $shopeeOptimal;
        $metaAllocation = $totalOptimal > 0 ? ($metaOptimal / $totalOptimal) * 100 : 50;
        $shopeeAllocation = $totalOptimal > 0 ? ($shopeeOptimal / $totalOptimal) * 100 : 50;
        
        return [
            'meta_optimal' => $metaOptimal,
            'shopee_optimal' => $shopeeOptimal,
            'total_optimal' => $totalOptimal,
            'meta_allocation' => round($metaAllocation, 1),
            'shopee_allocation' => round($shopeeAllocation, 1),
            'meta_performance' => $metaPerformance,
            'shopee_performance' => $shopeePerformance
        ];
    }

    private function generateOptimizedTrend($historicalData, $optimalValue, $totalPoints)
    {
        $n = count($historicalData);
        
        // Create trend that gradually moves toward optimal spending
        $trend = [];
        
        // Copy historical data
        for ($i = 0; $i < $n; $i++) {
            $trend[$i] = $historicalData[$i];
        }
        
        // Generate forward trend toward optimal value
        $lastValue = end($historicalData);
        $stepSize = ($optimalValue - $lastValue) / 10; // Gradual transition over 10 steps
        
        for ($i = $n; $i < $totalPoints; $i++) {
            $progress = ($i - $n + 1) / 4; // Progress over forecast period
            $newValue = $lastValue + ($stepSize * $progress * 4);
            
            // Ensure we don't overshoot the optimal value
            if ($stepSize > 0) {
                $newValue = min($newValue, $optimalValue);
            } else {
                $newValue = max($newValue, $optimalValue);
            }
            
            $trend[$i] = $newValue;
        }
        
        return $trend;
    }

    private function findOptimalSpendingLevel($performance)
    {
        // Strategy: Use the spending level that achieved the best ROAS
        // But ensure it's not too low (minimum viable spending)
        
        $optimalSpent = $performance['best_spent_for_roas'];
        $avgSpent = $performance['avg_spent'];
        
        // If best ROAS came from very low spending, use average instead
        if ($optimalSpent < $avgSpent * 0.3) {
            $optimalSpent = $avgSpent * 0.7; // Use 70% of average as conservative approach
        }
        
        // If performance is good, consider scaling up slightly
        if ($performance['best_roas'] > 3) {
            $optimalSpent *= 1.2; // Scale up by 20% for high-performing platforms
        } elseif ($performance['best_roas'] > 2) {
            $optimalSpent *= 1.1; // Scale up by 10% for good-performing platforms
        }
        
        // Ensure minimum spending level
        $minSpending = 1000000; // 1M IDR minimum
        $optimalSpent = max($optimalSpent, $minSpending);
        
        return round($optimalSpent, 0);
    }

    private function analyzeplatformPerformance($dailyData, $platform)
    {
        $spentKey = $platform . '_spent';
        $roasKey = $platform . '_roas';
        $salesKey = $platform . '_sales';
        
        // Filter out days with no spending
        $validDays = $dailyData->filter(function($day) use ($spentKey) {
            return $day[$spentKey] > 0;
        });
        
        if ($validDays->isEmpty()) {
            return [
                'avg_roas' => 0,
                'avg_spent' => 0,
                'avg_sales' => 0,
                'best_roas' => 0,
                'best_spent_for_roas' => 0,
                'efficiency_score' => 0
            ];
        }
        
        $avgRoas = $validDays->avg($roasKey);
        $avgSpent = $validDays->avg($spentKey);
        $avgSales = $validDays->avg($salesKey);
        
        // Find the spending level that gives best ROAS
        $bestPerformance = $validDays->sortByDesc($roasKey)->first();
        $bestRoas = $bestPerformance[$roasKey];
        $bestSpentForRoas = $bestPerformance[$spentKey];
        
        // Calculate efficiency score (ROAS * spending volume normalized)
        $maxSpent = $validDays->max($spentKey);
        $efficiencyScore = $maxSpent > 0 ? ($avgRoas * ($avgSpent / $maxSpent)) : 0;
        
        return [
            'avg_roas' => round($avgRoas, 2),
            'avg_spent' => round($avgSpent, 0),
            'avg_sales' => round($avgSales, 0),
            'best_roas' => round($bestRoas, 2),
            'best_spent_for_roas' => round($bestSpentForRoas, 0),
            'efficiency_score' => round($efficiencyScore, 3)
        ];
    }


    private function calculateMovingAverageForecast($data, $days)
    {
        $n = count($data);
        if ($n < 7) {
            // Not enough data, use simple average
            $avg = array_sum($data) / $n;
            return array_fill(0, $days, $avg);
        }
        
        // Calculate trend from last 14 days (or available data)
        $trendDays = min(14, $n);
        $recentData = array_slice($data, -$trendDays);
        
        // Simple linear trend calculation
        $x = range(0, $trendDays - 1);
        $y = $recentData;
        
        $trend = $this->calculateLinearTrend($x, $y);
        
        // Calculate baseline from last 7 days average
        $baseline = array_sum(array_slice($data, -7)) / 7;
        
        // Generate forecast
        $forecast = [];
        for ($i = 1; $i <= $days; $i++) {
            $trendValue = $trend['slope'] * ($trendDays + $i - 1) + $trend['intercept'];
            
            // Blend trend with baseline (60% baseline, 40% trend)
            $forecastValue = $baseline * 0.6 + $trendValue * 0.4;
            
            // Ensure positive values and reasonable bounds
            $forecastValue = max(0, $forecastValue);
            $forecastValue = min($forecastValue, max($data) * 1.5); // Cap at 150% of historical max
            
            $forecast[] = $forecastValue;
        }
        
        return $forecast;
    }

    private function calculateLinearTrend($x, $y)
    {
        $n = count($x);
        if ($n < 2) {
            return ['slope' => 0, 'intercept' => array_sum($y) / count($y)];
        }
        
        $sumX = array_sum($x);
        $sumY = array_sum($y);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $x[$i] * $y[$i];
            $sumX2 += $x[$i] * $x[$i];
        }
        
        $denominator = ($n * $sumX2 - $sumX * $sumX);
        if ($denominator == 0) {
            return ['slope' => 0, 'intercept' => $sumY / $n];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / $denominator;
        $intercept = ($sumY - $slope * $sumX) / $n;
        
        return ['slope' => $slope, 'intercept' => $intercept];
    }

    private function fitLogisticCurve($dates, $values)
    {
        $n = count($values);
        if ($n < 3) {
            return ['L' => max($values), 'k' => 0.1, 'x0' => $n/2];
        }
        
        // Simple logistic curve fitting
        // L = maximum value (carrying capacity)
        // k = growth rate
        // x0 = x-value of the sigmoid's midpoint
        
        $L = max($values) * 1.1; // Slightly above maximum
        $minVal = min($values);
        
        // If all values are the same, return flat curve
        if ($L - $minVal < 1) {
            return ['L' => $L, 'k' => 0, 'x0' => $n/2];
        }
        
        // Find midpoint
        $midValue = ($L + $minVal) / 2;
        $x0 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            if ($values[$i] >= $midValue) {
                $x0 = $i;
                break;
            }
        }
        
        // Estimate growth rate
        $k = 0.1; // Default moderate growth rate
        
        // Try to fit based on the steepest part of the curve
        for ($i = 1; $i < $n - 1; $i++) {
            $slope = abs($values[$i + 1] - $values[$i - 1]) / 2;
            if ($slope > 0) {
                $k = max($k, $slope / ($L - $minVal) * 4);
            }
        }
        
        return ['L' => $L, 'k' => min($k, 1), 'x0' => $x0];
    }

    private function logisticFunction($x, $params)
    {
        $L = $params['L'];
        $k = $params['k'];
        $x0 = $params['x0'];
        
        if ($k == 0) {
            return $L / 2; // Return midpoint for flat curve
        }
        
        return $L / (1 + exp(-$k * ($x - $x0)));
    }

    private function calculateTodayKPI($logisticData)
    {
        // Use the first forecast day as "today's ideal"
        $metaIdeal = $logisticData['meta_forecast'][0] ?? 0;
        $shopeeIdeal = $logisticData['shopee_forecast'][0] ?? 0;
        $totalIdeal = $metaIdeal + $shopeeIdeal;
        
        // Calculate ratio
        $ratio = '1:1';
        if ($shopeeIdeal > 0 && $metaIdeal > 0) {
            $metaRatio = round($metaIdeal / $shopeeIdeal, 1);
            $shopeeRatio = 1;
            
            if ($metaRatio < 1) {
                $shopeeRatio = round(1 / $metaRatio, 1);
                $metaRatio = 1;
            }
            
            $ratio = $metaRatio . ':' . $shopeeRatio;
        } elseif ($metaIdeal > 0) {
            $ratio = '1:0';
        } elseif ($shopeeIdeal > 0) {
            $ratio = '0:1';
        }
        
        return [
            'total_ideal_spent' => $totalIdeal,
            'meta_ideal_spent' => $metaIdeal,
            'shopee_ideal_spent' => $shopeeIdeal,
            'platform_ratio' => $ratio
        ];
    }

    private function calculateSKUBreakdown($historicalData, $logisticData)
    {
        // SKU name mapping
        $skuLabels = [
            'CLE-RS-047' => 'Red Saviour',
            'CLE-JB30-001' => 'Jelly Booster',
            'CL-GS' => 'Glowsmooth',
            'CLE-XFO-008' => '3 Minutes',
            'CLE-CLNDLA-025' => 'Calendula',
            'CLE-NEG-071' => 'Natural Exfo',
            'CL-TNR' => 'Pore Glow',
            'CL-8XHL' => '8X Hyalu',
            '-' => 'Other Products'
        ];
        
        // Calculate total spent for proportion calculation
        $totalSpent = $historicalData->sum('marketing');
        
        // Group historical data by SKU and platform
        $skuData = $historicalData->groupBy('sku')->map(function ($skuItems, $sku) use ($logisticData, $skuLabels, $totalSpent) {
            $platformData = $skuItems->groupBy('platform');
            
            // Calculate proportion of this SKU relative to total spending
            $skuTotalSpent = $skuItems->sum('marketing');
            $skuProportion = $totalSpent > 0 ? $skuTotalSpent / $totalSpent : 0;
            
            // Calculate platform distribution for this SKU
            $metaSpent = $platformData->get('Meta Ads', collect())->sum('marketing');
            $shopeeSpent = $platformData->get('Shopee Ads', collect())->sum('marketing');
            $skuTotal = $metaSpent + $shopeeSpent;
            
            $metaProportion = $skuTotal > 0 ? $metaSpent / $skuTotal : 0.5;
            $shopeeProportion = $skuTotal > 0 ? $shopeeSpent / $skuTotal : 0.5;
            
            // Apply proportions to ideal spending
            $metaForecastTotal = end($logisticData['meta_forecast']) ?: 0;
            $shopeeForecastTotal = end($logisticData['shopee_forecast']) ?: 0;
            $totalIdealSpent = $metaForecastTotal + $shopeeForecastTotal;
            $skuIdealSpent = $totalIdealSpent * $skuProportion;
            
            $metaIdealSpent = $skuIdealSpent * $metaProportion;
            $shopeeIdealSpent = $skuIdealSpent * $shopeeProportion;
            
            // Calculate ratio
            $ratio = '1:1';
            if ($shopeeIdealSpent > 0 && $metaIdealSpent > 0) {
                $metaRatio = round($metaIdealSpent / $shopeeIdealSpent, 1);
                $shopeeRatio = 1;
                
                if ($metaRatio < 1) {
                    $shopeeRatio = round(1 / $metaRatio, 1);
                    $metaRatio = 1;
                }
                
                $ratio = $metaRatio . ':' . $shopeeRatio;
            } elseif ($metaIdealSpent > 0) {
                $ratio = '1:0';
            } elseif ($shopeeIdealSpent > 0) {
                $ratio = '0:1';
            }
            
            return [
                'sku' => $sku,
                'product_name' => $skuLabels[$sku] ?? $sku,
                'total_ideal_spent' => $skuIdealSpent,
                'meta_ideal_spent' => $metaIdealSpent,
                'shopee_ideal_spent' => $shopeeIdealSpent,
                'ratio' => $ratio,
                'proportion' => $skuProportion
            ];
        });
        
        // Sort by total ideal spent descending
        return $skuData->sortByDesc('total_ideal_spent')->values()->toArray();
    }
}