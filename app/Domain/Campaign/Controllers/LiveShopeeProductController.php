<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\LiveShopeeProduct;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use Carbon\Carbon;
use Auth;
use DB;
use Log;

class LiveShopeeProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get distinct users for filter dropdown
        $userList = LiveShopeeProduct::select('user_id')
            ->distinct()
            ->where('user_id', '!=', '')
            ->pluck('user_id');
        
        return view('admin.live_shopee_product.index', compact('userList'));
    }

    /**
     * Get Live Shopee Product data for DataTables
     */
    public function get_live_shopee_product(Request $request) 
    {
        $query = LiveShopeeProduct::query()
            ->select([
                DB::raw('periode_data'),
                DB::raw('COUNT(*) as total_products'),
                DB::raw('SUM(klik_produk) as total_clicks'),
                DB::raw('SUM(tambah_ke_keranjang) as total_add_to_cart'),
                DB::raw('SUM(pesanan_dibuat) as total_orders_created'),
                DB::raw('SUM(pesanan_siap_dikirim) as total_orders_ready'),
                DB::raw('SUM(produk_terjual_siap_dikirim) as total_products_sold'),
                DB::raw('SUM(penjualan_dibuat) as total_sales_created'),
                DB::raw('SUM(penjualan_siap_dikirim) as total_sales_ready'),
                DB::raw('AVG(ranking) as avg_ranking')
            ])
            ->groupBy('periode_data');

        // Apply filters
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('periode_data', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('periode_data', now()->month)
                ->whereYear('periode_data', now()->year);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('periode_data', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->periode_data.'">'.
                       Carbon::parse($row->periode_data)->format('d M Y').'</a>';
            })
            ->editColumn('total_products', function ($row) {
                return $row->total_products ?? 0;
            })
            ->editColumn('total_clicks', function ($row) {
                return $row->total_clicks ? number_format($row->total_clicks, 0, ',', '.') : '0';
            })
            ->editColumn('total_add_to_cart', function ($row) {
                return $row->total_add_to_cart ? number_format($row->total_add_to_cart, 0, ',', '.') : '0';
            })
            ->editColumn('total_orders_created', function ($row) {
                return $row->total_orders_created ? number_format($row->total_orders_created, 0, ',', '.') : '0';
            })
            ->editColumn('total_orders_ready', function ($row) {
                return $row->total_orders_ready ? number_format($row->total_orders_ready, 0, ',', '.') : '0';
            })
            ->editColumn('total_products_sold', function ($row) {
                return $row->total_products_sold ? number_format($row->total_products_sold, 0, ',', '.') : '0';
            })
            ->editColumn('total_sales_created', function ($row) {
                return $row->total_sales_created ? 'Rp ' . number_format($row->total_sales_created, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_sales_ready', function ($row) {
                return $row->total_sales_ready ? 'Rp ' . number_format($row->total_sales_ready, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('avg_ranking', function ($row) {
                return $row->avg_ranking ? number_format($row->avg_ranking, 1) : '-';
            })
            ->addColumn('click_to_cart_rate', function ($row) {
                if ($row->total_clicks > 0 && $row->total_add_to_cart > 0) {
                    return number_format(($row->total_add_to_cart / $row->total_clicks) * 100, 2) . '%';
                }
                return '0%';
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->total_clicks > 0 && $row->total_orders_created > 0) {
                    return number_format(($row->total_orders_created / $row->total_clicks) * 100, 2) . '%';
                }
                return '0%';
            })
            ->addColumn('avg_order_value', function ($row) {
                if ($row->total_orders_created > 0 && $row->total_sales_created > 0) {
                    return 'Rp ' . number_format($row->total_sales_created / $row->total_orders_created, 0, ',', '.');
                }
                return 'Rp 0';
            })
            ->rawColumns(['periode_data'])
            ->make(true);
    }

    /**
     * Get Live Shopee Product details by date
     */
    public function get_live_shopee_product_details_by_date(Request $request) 
    {
        $query = LiveShopeeProduct::query()
            ->select([
                'id',
                'periode_data',
                'user_id',
                'ranking',
                'produk',
                'klik_produk',
                'tambah_ke_keranjang',
                'pesanan_dibuat',
                'pesanan_siap_dikirim',
                'produk_terjual_siap_dikirim',
                'penjualan_dibuat',
                'penjualan_siap_dikirim'
            ]);

        if ($request->has('date_start') && $request->has('date_end')) {
            try {
                $dateStart = Carbon::parse($request->input('date_start'))->format('Y-m-d');
                $dateEnd = Carbon::parse($request->input('date_end'))->format('Y-m-d');
                $query->whereBetween('periode_data', [$dateStart, $dateEnd]);
            } catch (\Exception $e) {
                Log::error('Date parsing error: ' . $e->getMessage());
            }
        } elseif ($request->has('date')) {
            try {
                $parsedDate = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('periode_data', $parsedDate);
            } catch (\Exception $e) {
                $query->where('periode_data', $request->input('date'));
            }
        }

        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('produk', function ($row) {
                return $row->produk ? '<div style="max-width: 300px; word-wrap: break-word;">' . $row->produk . '</div>' : '-';
            })
            ->editColumn('ranking', function ($row) {
                return $row->ranking ? '#' . $row->ranking : '-';
            })
            ->editColumn('klik_produk', function ($row) {
                return $row->klik_produk ? number_format($row->klik_produk, 0, ',', '.') : '-';
            })
            ->editColumn('tambah_ke_keranjang', function ($row) {
                return $row->tambah_ke_keranjang ? number_format($row->tambah_ke_keranjang, 0, ',', '.') : '-';
            })
            ->editColumn('pesanan_dibuat', function ($row) {
                return $row->pesanan_dibuat ? number_format($row->pesanan_dibuat, 0, ',', '.') : '-';
            })
            ->editColumn('pesanan_siap_dikirim', function ($row) {
                return $row->pesanan_siap_dikirim ? number_format($row->pesanan_siap_dikirim, 0, ',', '.') : '-';
            })
            ->editColumn('produk_terjual_siap_dikirim', function ($row) {
                return $row->produk_terjual_siap_dikirim ? number_format($row->produk_terjual_siap_dikirim, 0, ',', '.') : '-';
            })
            ->editColumn('penjualan_dibuat', function ($row) {
                return $row->penjualan_dibuat ? 'Rp ' . number_format($row->penjualan_dibuat, 0, ',', '.') : '-';
            })
            ->editColumn('penjualan_siap_dikirim', function ($row) {
                return $row->penjualan_siap_dikirim ? 'Rp ' . number_format($row->penjualan_siap_dikirim, 0, ',', '.') : '-';
            })
            ->addColumn('click_to_cart_rate', function ($row) {
                if ($row->klik_produk > 0 && $row->tambah_ke_keranjang > 0) {
                    $rate = ($row->tambah_ke_keranjang / $row->klik_produk) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->klik_produk > 0 && $row->pesanan_dibuat > 0) {
                    $rate = ($row->pesanan_dibuat / $row->klik_produk) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('avg_order_value', function ($row) {
                if ($row->pesanan_dibuat > 0 && $row->penjualan_dibuat > 0) {
                    $aov = $row->penjualan_dibuat / $row->pesanan_dibuat;
                    return 'Rp ' . number_format(floor($aov), 0, ',', '.');
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-danger btn-sm delete-product" data-id="'.$row->id.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['produk', 'action'])
            ->make(true);
    }

    /**
     * Get line chart data for product clicks over time
     */
    public function get_line_data(Request $request)
    {
        try {
            $query = LiveShopeeProduct::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('periode_data', [$startDate, $endDate]);
            } else {
                $query->where('periode_data', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
            }
            
            // Apply user filter
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Group by date and get sum of clicks
            $clicksData = $query->select(
                'periode_data',
                DB::raw('SUM(klik_produk) as total_clicks')
            )
            ->groupBy('periode_data')
            ->orderBy('periode_data')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->periode_data)->format('d M Y'),
                    'clicks' => (int)$item->total_clicks
                ];
            });
            
            if ($clicksData->isEmpty()) {
                $clicksData = collect([]);
            }
            
            return response()->json([
                'status' => 'success',
                'clicks' => $clicksData,
                'has_data' => $clicksData->isNotEmpty()
            ]);
            
        } catch (\Exception $e) {
            Log::error('Line chart data error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get funnel data for product conversion analysis
     */
    public function get_funnel_data(Request $request)
    {
        try {
            $query = LiveShopeeProduct::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('periode_data', [$startDate, $endDate]);
            } else {
                $query->where('periode_data', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
            }
            
            // Apply user filter
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(klik_produk) as total_clicks'),
                DB::raw('SUM(tambah_ke_keranjang) as total_add_to_cart'),
                DB::raw('SUM(pesanan_dibuat) as total_orders_created'),
                DB::raw('SUM(pesanan_siap_dikirim) as total_orders_ready'),
                DB::raw('SUM(produk_terjual_siap_dikirim) as total_products_sold')
            )->first();
            
            // Handle empty data case
            if (!$aggregates || $aggregates->total_clicks == 0) {
                $funnelData = [
                    ['name' => 'Product Clicks', 'value' => 0],
                    ['name' => 'Add to Cart', 'value' => 0],
                    ['name' => 'Orders Created', 'value' => 0],
                    ['name' => 'Orders Ready', 'value' => 0],
                    ['name' => 'Products Sold', 'value' => 0]
                ];
            } else {
                // Prepare data for funnel chart
                $funnelData = [
                    ['name' => 'Product Clicks', 'value' => (int)$aggregates->total_clicks],
                    ['name' => 'Add to Cart', 'value' => (int)$aggregates->total_add_to_cart],
                    ['name' => 'Orders Created', 'value' => (int)$aggregates->total_orders_created],
                    ['name' => 'Orders Ready', 'value' => (int)$aggregates->total_orders_ready],
                    ['name' => 'Products Sold', 'value' => (int)$aggregates->total_products_sold]
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData,
                'has_data' => $aggregates && $aggregates->total_clicks > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Import Live Shopee Product data from CSV
     */
    public function import_live_shopee_product(Request $request)
    {
        try {
            $request->validate([
                'live_shopee_product_csv_file' => 'required|file|mimes:csv,txt|max:5120'
            ]);

            $file = $request->file('live_shopee_product_csv_file');
            $importCount = 0;

            DB::beginTransaction();
            try {
                $csvData = array_map('str_getcsv', file($file->getPathname()));
                $headers = array_shift($csvData); // Remove header row
                
                Log::info('CSV Headers:', $headers);
                
                // Helper function to convert European number format to integer
                $convertToInteger = function($value) {
                    if (empty($value)) return 0;
                    
                    // Handle European format where periods are thousands separators
                    $cleanValue = str_replace('.', '', $value);
                    return (int)$cleanValue;
                };
                
                foreach ($csvData as $rowIndex => $row) {
                    if (empty($row) || count($row) < 11) {
                        Log::warning("Skipping row {$rowIndex}: insufficient data", $row);
                        continue;
                    }
                    
                    try {
                        // Parse the date from "Periode Data" column (Column A - index 0)
                        $periodeData = trim($row[0]);
                        $date = Carbon::createFromFormat('d-m-Y', $periodeData)->format('Y-m-d');
                        
                        // Get user_id from Column B (index 1)
                        $userId = trim($row[1]);
                        
                        // Get ranking from Column C (index 2)
                        $ranking = !empty(trim($row[2])) ? (int)trim($row[2]) : null;
                        
                        // Get product name from Column D (index 3)
                        $produk = trim($row[3]) ?: null;
                        
                        // Parse sales amounts - remove "Rp" and convert to numeric
                        $penjualanDibuat = 0;
                        $penjualanSiapDikirim = 0;
                        
                        if (!empty(trim($row[9]))) { // Column J
                            $penjualanDibuat = (float)preg_replace('/[^\d]/', '', $row[9]);
                        }
                        
                        if (!empty(trim($row[10]))) { // Column K
                            $penjualanSiapDikirim = (float)preg_replace('/[^\d]/', '', $row[10]);
                        }
                        
                        LiveShopeeProduct::updateOrCreate(
                            [
                                'periode_data' => $date,
                                'user_id' => $userId,
                                'ranking' => $ranking,
                                'produk' => $produk
                            ],
                            [
                                'klik_produk' => $convertToInteger($row[4]), // Column E
                                'tambah_ke_keranjang' => $convertToInteger($row[5]), // Column F
                                'pesanan_dibuat' => (int)($row[6] ?? 0), // Column G
                                'pesanan_siap_dikirim' => (int)($row[7] ?? 0), // Column H
                                'produk_terjual_siap_dikirim' => (int)($row[8] ?? 0), // Column I
                                'penjualan_dibuat' => $penjualanDibuat,
                                'penjualan_siap_dikirim' => $penjualanSiapDikirim
                            ]
                        );
                        
                        $importCount++;
                        
                        Log::info("Successfully imported row {$rowIndex}", [
                            'date' => $date,
                            'user_id' => $userId,
                            'ranking' => $ranking,
                            'product' => $produk,
                            'clicks' => $convertToInteger($row[4]),
                            'orders' => $row[6],
                            'sales' => $penjualanDibuat
                        ]);
                        
                    } catch (\Exception $e) {
                        Log::error("Error processing row {$rowIndex}: " . $e->getMessage(), [
                            'row_data' => $row,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        continue; // Skip this row and continue with the next
                    }
                }
                
                DB::commit();
                
                Log::info("Import completed", [
                    'total_imported' => $importCount,
                    'file_name' => $file->getClientOriginalName()
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Live Shopee Product data imported successfully. ' . $importCount . ' records imported.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Import transaction failed: " . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error("Import failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete Live Shopee Product record
     */
    public function delete_live_shopee_product(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $liveShopeeProduct = LiveShopeeProduct::find($id);
            if (!$liveShopeeProduct) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Product record not found'
                ], 404);
            }
            
            $liveShopeeProduct->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Product record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 422);
        }
    }
}