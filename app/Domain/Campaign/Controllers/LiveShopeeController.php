<?php

namespace App\Domain\Campaign\Controllers;

use App\Domain\Campaign\Models\LiveShopee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use App\Domain\Sales\Models\SalesChannel;
use Auth;
use DB;
use Log;

class LiveShopeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get distinct users for filter dropdown
        $userList = LiveShopee::select('user_id')
            ->distinct()
            ->where('user_id', '!=', '')
            ->pluck('user_id');
        
        return view('admin.live_shopee.index', compact('userList'));
    }

    /**
     * Get Live Shopee data for DataTables
     */
    public function get_live_shopee(Request $request) 
    {
        $query = LiveShopee::query()
            ->select([
                DB::raw('date'),
                DB::raw('COUNT(*) as total_streams'),
                DB::raw('SUM(durasi) as total_duration'),
                DB::raw('AVG(penonton_aktif) as avg_active_viewers'),
                DB::raw('SUM(penonton) as total_viewers'),
                DB::raw('SUM(komentar) as total_comments'),
                DB::raw('SUM(tambah_ke_keranjang) as total_add_to_cart'),
                DB::raw('SUM(pesanan_dibuat) as total_orders_created'),
                DB::raw('SUM(pesanan_siap_dikirim) as total_orders_ready'),
                DB::raw('SUM(produk_terjual_dibuat) as total_products_sold_created'),
                DB::raw('SUM(produk_terjual_siap_dikirim) as total_products_sold_ready'),
                DB::raw('SUM(penjualan_dibuat) as total_sales_created'),
                DB::raw('SUM(penjualan_siap_dikirim) as total_sales_ready'),
                DB::raw('AVG(rata_rata_durasi_ditonton) as avg_watch_duration')
            ])
            ->groupBy('date');

        // Apply tenant filter
        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }
        
        if ($request->has('user_id') && $request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->date.'">'.
                       Carbon::parse($row->date)->format('d M Y').'</a>';
            })
            ->editColumn('total_streams', function ($row) {
                return $row->total_streams ?? 0;
            })
            ->editColumn('total_duration', function ($row) {
                return $row->total_duration ?? 0;
            })
            ->editColumn('avg_active_viewers', function ($row) {
                return number_format($row->avg_active_viewers ?? 0, 0);
            })
            ->editColumn('total_viewers', function ($row) {
                return $row->total_viewers ?? 0;
            })
            ->editColumn('total_comments', function ($row) {
                return $row->total_comments ?? 0;
            })
            ->editColumn('total_add_to_cart', function ($row) {
                return $row->total_add_to_cart ?? 0;
            })
            ->editColumn('total_orders_created', function ($row) {
                return $row->total_orders_created ?? 0;
            })
            ->editColumn('total_orders_ready', function ($row) {
                return $row->total_orders_ready ?? 0;
            })
            ->editColumn('total_sales_created', function ($row) {
                return $row->total_sales_created ? 'Rp ' . number_format($row->total_sales_created, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_sales_ready', function ($row) {
                return $row->total_sales_ready ? 'Rp ' . number_format($row->total_sales_ready, 0, ',', '.') : 'Rp 0';
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->total_viewers > 0 && $row->total_orders_created > 0) {
                    return number_format(($row->total_orders_created / $row->total_viewers) * 100, 2) . '%';
                }
                return '0%';
            })
            ->addColumn('avg_order_value', function ($row) {
                if ($row->total_orders_created > 0 && $row->total_sales_created > 0) {
                    return 'Rp ' . number_format($row->total_sales_created / $row->total_orders_created, 0, ',', '.');
                }
                return 'Rp 0';
            })
            ->addColumn('performance', function ($row) {
                // Performance based on conversion rate
                if ($row->total_viewers > 0 && $row->total_orders_created > 0) {
                    $conversionRate = ($row->total_orders_created / $row->total_viewers) * 100;
                    
                    if ($conversionRate >= 5) {
                        return '<span class="badge badge-success">Excellent</span>';
                    } elseif ($conversionRate >= 3) {
                        return '<span class="badge badge-primary">Good</span>';
                    } elseif ($conversionRate >= 1) {
                        return '<span class="badge badge-info">Average</span>';
                    } else {
                        return '<span class="badge badge-warning">Poor</span>';
                    }
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->rawColumns(['date', 'performance'])
            ->make(true);
    }

    /**
     * Get Live Shopee details by date
     */
    public function get_live_shopee_details_by_date(Request $request) 
    {
        $query = LiveShopee::query()
            ->select([
                'id',
                'date',
                'user_id',
                'no',
                'nama_livestream',
                'start_time',
                'durasi',
                'penonton_aktif',
                'komentar',
                'tambah_ke_keranjang',
                'rata_rata_durasi_ditonton',
                'penonton',
                'pesanan_dibuat',
                'pesanan_siap_dikirim',
                'produk_terjual_dibuat',
                'produk_terjual_siap_dikirim',
                'penjualan_dibuat',
                'penjualan_siap_dikirim'
            ]);

        if ($request->has('date_start') && $request->has('date_end')) {
            try {
                $dateStart = Carbon::parse($request->input('date_start'))->format('Y-m-d');
                $dateEnd = Carbon::parse($request->input('date_end'))->format('Y-m-d');
                $query->whereBetween('date', [$dateStart, $dateEnd]);
            } catch (\Exception $e) {
                Log::error('Date parsing error: ' . $e->getMessage());
            }
        } elseif ($request->has('date')) {
            try {
                $parsedDate = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('date', $parsedDate);
            } catch (\Exception $e) {
                $query->where('date', $request->input('date'));
            }
        }

        if (auth()->user() && auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }

        if ($request->has('user_id') && $request->user_id !== '') {
            $query->where('user_id', $request->user_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('start_time', function ($row) {
                return $row->start_time ?: '-';
            })
            ->editColumn('durasi', function ($row) {
                return $row->durasi ? $row->durasi . ' min' : '-';
            })
            ->editColumn('penonton_aktif', function ($row) {
                return $row->penonton_aktif ? number_format($row->penonton_aktif, 0, ',', '.') : '-';
            })
            ->editColumn('komentar', function ($row) {
                return $row->komentar ? number_format($row->komentar, 0, ',', '.') : '-';
            })
            ->editColumn('tambah_ke_keranjang', function ($row) {
                return $row->tambah_ke_keranjang ? number_format($row->tambah_ke_keranjang, 0, ',', '.') : '-';
            })
            ->editColumn('rata_rata_durasi_ditonton', function ($row) {
                return $row->rata_rata_durasi_ditonton ? number_format($row->rata_rata_durasi_ditonton, 2, ',', '.') . ' min' : '-';
            })
            ->editColumn('penonton', function ($row) {
                return $row->penonton ? number_format($row->penonton, 0, ',', '.') : '-';
            })
            ->editColumn('pesanan_dibuat', function ($row) {
                return $row->pesanan_dibuat ? number_format($row->pesanan_dibuat, 0, ',', '.') : '-';
            })
            ->editColumn('pesanan_siap_dikirim', function ($row) {
                return $row->pesanan_siap_dikirim ? number_format($row->pesanan_siap_dikirim, 0, ',', '.') : '-';
            })
            ->editColumn('produk_terjual_dibuat', function ($row) {
                return $row->produk_terjual_dibuat ? number_format($row->produk_terjual_dibuat, 0, ',', '.') : '-';
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
            ->addColumn('conversion_rate', function ($row) {
                if ($row->penonton > 0 && $row->pesanan_dibuat > 0) {
                    $rate = ($row->pesanan_dibuat / $row->penonton) * 100;
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
            ->addColumn('engagement_rate', function ($row) {
                if ($row->penonton > 0 && $row->komentar > 0) {
                    $rate = ($row->komentar / $row->penonton) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-danger btn-sm delete-stream" data-id="'.$row->id.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get line chart data for viewers over time
     */
    public function get_line_data(Request $request)
    {
        try {
            $query = LiveShopee::query();
            
            // Apply tenant filter
            if (auth()->user() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // Default to current month if no date filter
                $query->whereMonth('date', now()->month)
                      ->whereYear('date', now()->year);
            }
            
            // Apply user filter
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Group by date and get sum of viewers
            $viewersData = $query->select(
                'date',
                DB::raw('SUM(penonton) as total_viewers')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'viewers' => (int)$item->total_viewers
                ];
            });
            
            // If no data, return empty array
            if ($viewersData->isEmpty()) {
                $viewersData = collect([]);
            }
            
            return response()->json([
                'status' => 'success',
                'viewers' => $viewersData,
                'has_data' => $viewersData->isNotEmpty()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Get funnel data for conversion analysis
     */
    public function get_funnel_data(Request $request)
    {
        try {
            $query = LiveShopee::query();
            
            // Apply tenant filter
            if (auth()->user() && auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // Default to current month if no date filter
                $query->whereMonth('date', now()->month)
                      ->whereYear('date', now()->year);
            }
            
            // Apply user filter
            if ($request->has('user_id') && $request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(penonton) as total_viewers'),
                DB::raw('SUM(penonton_aktif) as total_active_viewers'),
                DB::raw('SUM(komentar) as total_comments'),
                DB::raw('SUM(tambah_ke_keranjang) as total_add_to_cart'),
                DB::raw('SUM(pesanan_dibuat) as total_orders')
            )->first();
            
            // Handle empty data case
            if (!$aggregates || $aggregates->total_viewers == 0) {
                $funnelData = [
                    ['name' => 'Total Viewers', 'value' => 0],
                    ['name' => 'Active Viewers', 'value' => 0],
                    ['name' => 'Comments', 'value' => 0],
                    ['name' => 'Add to Cart', 'value' => 0],
                    ['name' => 'Orders', 'value' => 0]
                ];
            } else {
                // Prepare data for funnel chart
                $funnelData = [
                    ['name' => 'Total Viewers', 'value' => (int)$aggregates->total_viewers],
                    ['name' => 'Active Viewers', 'value' => (int)$aggregates->total_active_viewers],
                    ['name' => 'Comments', 'value' => (int)$aggregates->total_comments],
                    ['name' => 'Add to Cart', 'value' => (int)$aggregates->total_add_to_cart],
                    ['name' => 'Orders', 'value' => (int)$aggregates->total_orders]
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData,
                'has_data' => $aggregates && $aggregates->total_viewers > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Import Live Shopee data from CSV
     */
    public function import_live_shopee(Request $request)
    {
        try {
            $request->validate([
                'live_shopee_csv_file' => 'required|file|mimes:csv,txt|max:5120'
            ]);

            $file = $request->file('live_shopee_csv_file');
            $importCount = 0;

            DB::beginTransaction();
            try {
                $csvData = array_map('str_getcsv', file($file->getPathname()));
                $headers = array_shift($csvData); // Remove header row
                
                // Log headers for debugging
                \Log::info('CSV Headers:', $headers);
                
                foreach ($csvData as $rowIndex => $row) {
                    if (empty($row) || count($row) < 17) {
                        \Log::warning("Skipping row {$rowIndex}: insufficient data", $row);
                        continue;
                    }
                    
                    try {
                        // Parse the date from "Periode Data" column (index 0)
                        $periodeData = trim($row[0]); // e.g., "01-05-2025"
                        $date = Carbon::createFromFormat('d-m-Y', $periodeData)->format('Y-m-d');
                        
                        // Get user_id from column index 1
                        $userId = trim($row[1]); // e.g., 215123036
                        
                        // Parse start time - extract just the time part
                        $startTimeRaw = trim($row[4]); // e.g., "01-05-2025 06:00"
                        $startTime = null;
                        if (strpos($startTimeRaw, ' ') !== false) {
                            $timeParts = explode(' ', $startTimeRaw);
                            $startTime = count($timeParts) > 1 ? $timeParts[1] . ':00' : null;
                        }
                        
                        // Parse duration from HH:MM:SS to minutes
                        $durasiRaw = trim($row[5]); // e.g., "19:00:04"
                        $durasi = 0;
                        if (preg_match('/(\d+):(\d+):(\d+)/', $durasiRaw, $matches)) {
                            $hours = (int)$matches[1];
                            $minutes = (int)$matches[2];
                            $seconds = (int)$matches[3];
                            $durasi = ($hours * 60) + $minutes + ($seconds > 30 ? 1 : 0); // Round seconds
                        }
                        
                        // Parse average watch duration from MM:SS to decimal minutes
                        $avgDurationRaw = trim($row[9]); // e.g., "00:01:31"
                        $avgDuration = 0;
                        if (preg_match('/(\d+):(\d+)/', $avgDurationRaw, $matches)) {
                            $minutes = (int)$matches[1];
                            $seconds = (int)$matches[2];
                            $avgDuration = $minutes + ($seconds / 60);
                        }
                        
                        // Parse sales amounts - remove "Rp" and convert to numeric
                        $penjualanDibuat = 0;
                        $penjualanSiapDikirim = 0;
                        
                        if (!empty(trim($row[15]))) {
                            $penjualanDibuat = (float)preg_replace('/[^\d]/', '', $row[15]);
                        }
                        
                        if (!empty(trim($row[16]))) {
                            $penjualanSiapDikirim = (float)preg_replace('/[^\d]/', '', $row[16]);
                        }
                        
                        // Determine tenant_id
                        $tenantId = null;
                        if (Auth::user()) {
                            $tenantId = Auth::user()->current_tenant_id ?? Auth::user()->tenant_id ?? 1;
                        }
                        
                        LiveShopee::updateOrCreate(
                            [
                                'date' => $date,
                                'user_id' => $userId,
                                'no' => trim($row[2]) ?: null, // No.
                                'nama_livestream' => trim($row[3]) ?: null, // Nama Livestream
                                'tenant_id' => $tenantId
                            ],
                            [
                                'start_time' => $startTime,
                                'durasi' => $durasi,
                                'penonton_aktif' => (int)($row[6] ?? 0), // Penonton Aktif
                                'komentar' => (int)($row[7] ?? 0), // Komentar
                                'tambah_ke_keranjang' => (int)($row[8] ?? 0), // Tambah ke Keranjang
                                'rata_rata_durasi_ditonton' => $avgDuration, // Rata-rata durasi ditonton
                                'penonton' => (int)($row[10] ?? 0), // Penonton
                                'pesanan_dibuat' => (int)($row[11] ?? 0), // Pesanan(Pesanan Dibuat)
                                'pesanan_siap_dikirim' => (int)($row[12] ?? 0), // Pesanan(Pesanan Siap Dikirim)
                                'produk_terjual_dibuat' => (int)($row[13] ?? 0), // Produk Terjual(Pesanan Dibuat)
                                'produk_terjual_siap_dikirim' => (int)($row[14] ?? 0), // Produk Terjual(Pesanan Siap Dikirim)
                                'penjualan_dibuat' => $penjualanDibuat, // Penjualan(Pesanan Dibuat)
                                'penjualan_siap_dikirim' => $penjualanSiapDikirim // Penjualan(Pesanan Siap Dikirim)
                            ]
                        );
                        
                        $importCount++;
                        
                        \Log::info("Successfully imported row {$rowIndex}", [
                            'date' => $date,
                            'user_id' => $userId,
                            'livestream' => $row[3],
                            'viewers' => $row[10],
                            'orders' => $row[11],
                            'sales' => $penjualanDibuat
                        ]);
                        
                    } catch (\Exception $e) {
                        \Log::error("Error processing row {$rowIndex}: " . $e->getMessage(), [
                            'row_data' => $row,
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        continue; // Skip this row and continue with the next
                    }
                }
                
                DB::commit();
                
                \Log::info("Import completed", [
                    'total_imported' => $importCount,
                    'file_name' => $file->getClientOriginalName()
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Live Shopee data imported successfully. ' . $importCount . ' records imported.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Import transaction failed: " . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error("Import failed: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Delete Live Shopee record
     */
    public function delete_live_shopee(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $liveShopee = LiveShopee::find($id);
            if (!$liveShopee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Live stream record not found'
                ], 404);
            }
            
            $liveShopee->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Live stream record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 422);
        }
    }
}