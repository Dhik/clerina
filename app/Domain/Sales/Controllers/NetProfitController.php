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
            // Define the range to get KOL spent data from column R
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
            foreach ($kolSpentData as $date => $amount) {
                NetProfit::updateOrCreate(
                    [
                        'date' => $date,
                        'tenant_id' => $tenant_id
                    ],
                    [
                        'spent_kol' => $amount
                    ]
                );
            }
            
            return response()->json(['success' => true, 'message' => 'KOL spent data updated successfully']);
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
            
            // Update net_profits table with the sales data
            foreach ($salesData as $date => $data) {
                NetProfit::updateOrCreate(
                    [
                        'date' => $date
                    ],
                    [
                        'tenant_id' => $tenant_id,
                        'b2b_sales' => $data['b2b_sales'],
                        'crm_sales' => $data['crm_sales'],
                        'updated_at' => now()
                    ]
                );
            }
            
            return response()->json([
                'success' => true, 
                'message' => 'B2B and CRM sales data updated successfully',
                'records_processed' => count($salesData)
            ]);
        } catch(\Exception $e) {
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
            $tenant_id = Auth::user()->current_tenant_id;
            
            $dates = collect();
            for($date = clone $startDate; $date->lte(now()); $date->addDay()) {
                $dates->push($date->format('Y-m-d'));
            }

            $hppPerDate = Order::query()
                ->whereBetween('orders.date', [$startDate, now()])
                ->where('orders.tenant_id', $tenant_id)
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
                ->where('tenant_id', $tenant_id)
                ->update(['hpp' => 0]);

            NetProfit::query()
                ->where('net_profits.tenant_id', $tenant_id)
                ->whereBetween('net_profits.date', [$startDate, now()])
                ->joinSub($hppPerDate, 'hpp', function($join) {
                    $join->on('net_profits.date', '=', 'hpp.date');
                })
                ->update(['hpp' => DB::raw('hpp.total_hpp')]);

            foreach($dates as $date) {
                $exists = NetProfit::where('date', $date)
                    ->where('tenant_id', $tenant_id)
                    ->exists();
                    
                if (!$exists) {
                    $hppValue = $hppPerDate->where('date', $date)->first();
                    NetProfit::create([
                        'date' => $date,
                        'tenant_id' => $tenant_id,
                        'hpp' => $hppValue ? $hppValue->total_hpp : 0
                    ]);
                }
            }

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
            DB::statement("
                UPDATE net_profits
                INNER JOIN sales ON net_profits.date = sales.date AND net_profits.tenant_id = sales.tenant_id
                SET net_profits.marketing = sales.ad_spent_total, 
                    net_profits.updated_at = ?
                WHERE MONTH(net_profits.date) = ?
                AND YEAR(net_profits.date) = ?
                AND sales.tenant_id = ?
            ", [now(), now()->month, now()->year, Auth::user()->current_tenant_id]);

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
                // $sales = $this->parseCurrencyToInt($row[1] ?? null);
                $affiliate = $this->parseCurrencyToInt($row[2] ?? null);
                $visit = $this->parseToInt($row[3] ?? null);

                NetProfit::updateOrCreate(
                    [
                        'date' => $date,
                        'tenant_id' => 1
                    ],
                    [
                        // 'sales' => $sales,
                        'affiliate' => $affiliate,
                        'visit' => $visit,
                        'tenant_id' => 1  // Ensure tenant_id is set to 1 for new records
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
            $tenant_id = Auth::user()->current_tenant_id;

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
            $tenant_id = Auth::user()->current_tenant_id;
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');

            $netProfitDates = NetProfit::whereBetween('date', [$startDate, $endDate])
                ->where('tenant_id', 1)
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
                    ->where('tenant_id', 1)
                    ->whereNotIn('status', $excludedStatuses)
                    ->sum('amount');

                // Update the net_profit record for this date and tenant
                $updated = NetProfit::where('date', $date)
                    ->where('tenant_id', 1)
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
            $tenant_id = Auth::user()->current_tenant_id;

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
            $tenant_id = Auth::user()->current_tenant_id;

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
    public function exportDateAndSales()
    {
        // Fetch only date and sales from NetProfit model
        $records = NetProfit::select('date', 'sales')
            ->orderBy('date')
            ->get();
        
        $data = [];
        
        // Add headers
        $data[] = ['Date', 'Sales'];
        
        // Add rows
        foreach ($records as $record) {
            $data[] = [
                $record->date->format('Y-m-d'),  // Format the date using Carbon
                $record->sales  // Sales field
            ];
        }
        
        $this->googleSheetService->clearRange('SalesReport!A1:B1000');
        $this->googleSheetService->exportData('SalesReport!A1', $data);
        
        return response()->json([
            'success' => true, 
            'message' => 'Date and sales data exported successfully',
            'count' => count($data) - 1 // Subtract 1 for header row
        ]);
    }
}