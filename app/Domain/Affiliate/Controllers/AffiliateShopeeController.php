<?php

namespace App\Domain\Affiliate\Controllers;

use App\Domain\Affiliate\Models\AffiliateShopee;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use App\Domain\Sales\Models\SalesChannel;
use Auth;
use DB;
use Log;

class AffiliateShopeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get distinct affiliates for filter dropdown
        $affiliateList = AffiliateShopee::select('affiliate_id')
            ->distinct()
            ->where('affiliate_id', '!=', '')
            ->pluck('affiliate_id');
        
        return view('admin.affiliate_shopee.index', compact('affiliateList'));
    }

    /**
     * Get Affiliate Shopee data for DataTables
     */
    public function get_affiliate_shopee(Request $request) 
    {
        $query = AffiliateShopee::query()
            ->select([
                DB::raw('date'),
                DB::raw('COUNT(*) as total_affiliates'),
                DB::raw('SUM(omzet_penjualan) as total_omzet'),
                DB::raw('SUM(produk_terjual) as total_products_sold'),
                DB::raw('SUM(pesanan) as total_orders'),
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(estimasi_komisi) as total_estimated_commission'),
                DB::raw('AVG(roi) as avg_roi'),
                DB::raw('SUM(total_pembeli) as total_buyers'),
                DB::raw('SUM(pembeli_baru) as total_new_buyers')
            ])
            ->groupBy('date');

        // Apply filters
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }
        
        if ($request->has('affiliate_id') && $request->affiliate_id) {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->date.'">'.
                       Carbon::parse($row->date)->format('d M Y').'</a>';
            })
            ->editColumn('total_affiliates', function ($row) {
                return $row->total_affiliates ?? 0;
            })
            ->editColumn('total_omzet', function ($row) {
                return $row->total_omzet ? 'Rp ' . number_format($row->total_omzet, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_products_sold', function ($row) {
                return $row->total_products_sold ?? 0;
            })
            ->editColumn('total_orders', function ($row) {
                return $row->total_orders ?? 0;
            })
            ->editColumn('total_clicks', function ($row) {
                return $row->total_clicks ?? 0;
            })
            ->editColumn('total_estimated_commission', function ($row) {
                return $row->total_estimated_commission ? 'Rp ' . number_format($row->total_estimated_commission, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('avg_roi', function ($row) {
                return $row->avg_roi ? number_format($row->avg_roi, 2, ',', '.') . '%' : '0%';
            })
            ->editColumn('total_buyers', function ($row) {
                return $row->total_buyers ?? 0;
            })
            ->editColumn('total_new_buyers', function ($row) {
                return $row->total_new_buyers ?? 0;
            })
            ->addColumn('click_through_rate', function ($row) {
                if ($row->total_clicks > 0 && $row->total_orders > 0) {
                    return number_format(($row->total_orders / $row->total_clicks) * 100, 2) . '%';
                }
                return '0%';
            })
            ->addColumn('avg_commission_per_affiliate', function ($row) {
                if ($row->total_affiliates > 0 && $row->total_estimated_commission > 0) {
                    return 'Rp ' . number_format($row->total_estimated_commission / $row->total_affiliates, 0, ',', '.');
                }
                return 'Rp 0';
            })
            ->addColumn('performance', function ($row) {
                // Performance based on ROI
                if ($row->avg_roi > 0) {
                    if ($row->avg_roi >= 15) {
                        return '<span class="badge badge-success">Excellent</span>';
                    } elseif ($row->avg_roi >= 10) {
                        return '<span class="badge badge-primary">Good</span>';
                    } elseif ($row->avg_roi >= 5) {
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
     * Get Affiliate Shopee details by date
     */
    public function get_affiliate_shopee_details_by_date(Request $request) 
    {
        $query = AffiliateShopee::query()
            ->select([
                'id',
                'date',
                'affiliate_id',
                'nama_affiliate',
                'username_affiliate',
                'omzet_penjualan',
                'produk_terjual',
                'pesanan',
                'clicks',
                'estimasi_komisi',
                'roi',
                'total_pembeli',
                'pembeli_baru'
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

        if ($request->has('affiliate_id') && $request->affiliate_id !== '') {
            $query->where('affiliate_id', $request->affiliate_id);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('omzet_penjualan', function ($row) {
                return $row->omzet_penjualan ? 'Rp ' . number_format($row->omzet_penjualan, 0, ',', '.') : '-';
            })
            ->editColumn('produk_terjual', function ($row) {
                return $row->produk_terjual ? number_format($row->produk_terjual, 0, ',', '.') : '-';
            })
            ->editColumn('pesanan', function ($row) {
                return $row->pesanan ? number_format($row->pesanan, 0, ',', '.') : '-';
            })
            ->editColumn('clicks', function ($row) {
                return $row->clicks ? number_format($row->clicks, 0, ',', '.') : '-';
            })
            ->editColumn('estimasi_komisi', function ($row) {
                return $row->estimasi_komisi ? 'Rp ' . number_format($row->estimasi_komisi, 0, ',', '.') : '-';
            })
            ->editColumn('roi', function ($row) {
                return $row->roi ? number_format($row->roi, 2, ',', '.') . '%' : '-';
            })
            ->editColumn('total_pembeli', function ($row) {
                return $row->total_pembeli ? number_format($row->total_pembeli, 0, ',', '.') : '-';
            })
            ->editColumn('pembeli_baru', function ($row) {
                return $row->pembeli_baru ? number_format($row->pembeli_baru, 0, ',', '.') : '-';
            })
            ->addColumn('click_through_rate', function ($row) {
                if ($row->clicks > 0 && $row->pesanan > 0) {
                    $rate = ($row->pesanan / $row->clicks) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('commission_rate', function ($row) {
                if ($row->omzet_penjualan > 0 && $row->estimasi_komisi > 0) {
                    $rate = ($row->estimasi_komisi / $row->omzet_penjualan) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('new_buyer_rate', function ($row) {
                if ($row->total_pembeli > 0 && $row->pembeli_baru > 0) {
                    $rate = ($row->pembeli_baru / $row->total_pembeli) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-danger btn-sm delete-affiliate" data-id="'.$row->id.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Get line chart data for commission over time
     */
    public function get_line_data(Request $request)
    {
        try {
            $query = AffiliateShopee::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
                
                \Log::info('Line chart date filter applied', [
                    'filter_dates' => $request->filterDates,
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]);
            } else {
                // If no date filter, show data from the last 30 days OR all data if less than 30 days
                $query->where('date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
                      
                \Log::info('Line chart default date filter applied', [
                    'showing_last_30_days_from' => Carbon::now()->subDays(30)->format('Y-m-d')
                ]);
            }
            
            // Apply affiliate filter
            if ($request->has('affiliate_id') && $request->affiliate_id) {
                $query->where('affiliate_id', $request->affiliate_id);
                \Log::info('Line chart affiliate filter applied', ['affiliate_id' => $request->affiliate_id]);
            }
            
            // Debug: Get total count before grouping
            $totalRecords = AffiliateShopee::count();
            $filteredCount = (clone $query)->count();
            
            \Log::info('Line chart query debug', [
                'total_records_in_table' => $totalRecords,
                'filtered_records_count' => $filteredCount,
                'has_filter_dates' => $request->has('filterDates'),
                'filter_dates_value' => $request->filterDates,
                'has_affiliate_filter' => $request->has('affiliate_id'),
                'affiliate_filter_value' => $request->affiliate_id
            ]);
            
            // Group by date and get sum of commission
            $commissionData = $query->select(
                'date',
                DB::raw('SUM(estimasi_komisi) as total_commission')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'commission' => (int)$item->total_commission
                ];
            });
            
            \Log::info('Line chart data result', [
                'data_count' => $commissionData->count(),
                'data' => $commissionData->toArray()
            ]);
            
            // If no data, return empty array
            if ($commissionData->isEmpty()) {
                $commissionData = collect([]);
            }
            
            return response()->json([
                'status' => 'success',
                'commission' => $commissionData,
                'has_data' => $commissionData->isNotEmpty(),
                'debug' => [
                    'total_records' => $totalRecords,
                    'filtered_count' => $filteredCount,
                    'result_count' => $commissionData->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Line chart data error', [
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
     * Get funnel data for affiliate analysis
     */
    public function get_funnel_data(Request $request)
    {
        try {
            $query = AffiliateShopee::query();
            
            // Apply date filter
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $query->whereBetween('date', [$startDate, $endDate]);
            } else {
                // If no date filter, show data from the last 30 days OR all data if less than 30 days
                $query->where('date', '>=', Carbon::now()->subDays(30)->format('Y-m-d'));
            }
            
            // Apply affiliate filter
            if ($request->has('affiliate_id') && $request->affiliate_id) {
                $query->where('affiliate_id', $request->affiliate_id);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(clicks) as total_clicks'),
                DB::raw('SUM(total_pembeli) as total_buyers'),
                DB::raw('SUM(pesanan) as total_orders'),
                DB::raw('SUM(produk_terjual) as total_products_sold'),
                DB::raw('SUM(estimasi_komisi) as total_commission')
            )->first();
            
            // Handle empty data case
            if (!$aggregates || $aggregates->total_clicks == 0) {
                $funnelData = [
                    ['name' => 'Total Clicks', 'value' => 0],
                    ['name' => 'Total Buyers', 'value' => 0],
                    ['name' => 'Total Orders', 'value' => 0],
                    ['name' => 'Products Sold', 'value' => 0],
                    ['name' => 'Commission (K)', 'value' => 0]
                ];
            } else {
                // Prepare data for funnel chart
                $funnelData = [
                    ['name' => 'Total Clicks', 'value' => (int)$aggregates->total_clicks],
                    ['name' => 'Total Buyers', 'value' => (int)$aggregates->total_buyers],
                    ['name' => 'Total Orders', 'value' => (int)$aggregates->total_orders],
                    ['name' => 'Products Sold', 'value' => (int)$aggregates->total_products_sold],
                    ['name' => 'Commission (K)', 'value' => (int)($aggregates->total_commission / 1000)]
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
     * Import Affiliate Shopee data from CSV
     */
    public function import_affiliate_shopee(Request $request)
    {
        try {
            $request->validate([
                'affiliate_shopee_csv_file' => 'required|file|mimes:csv,txt|max:5120',
                'import_date' => 'required|date'
            ]);

            $file = $request->file('affiliate_shopee_csv_file');
            $importDate = Carbon::parse($request->input('import_date'))->format('Y-m-d');
            $importCount = 0;

            DB::beginTransaction();
            try {
                $csvData = array_map('str_getcsv', file($file->getPathname()));
                $headers = array_shift($csvData); // Remove header row
                
                // Log headers for debugging
                \Log::info('CSV Headers:', $headers);
                
                // Helper function to convert number format to integer
                $convertToInteger = function($value) {
                    if (empty($value)) return 0;
                    
                    // Remove any non-numeric characters except decimal points
                    $cleanValue = preg_replace('/[^\d.]/', '', $value);
                    return (int)$cleanValue;
                };
                
                // Helper function to convert number format to decimal
                $convertToDecimal = function($value) {
                    if (empty($value)) return 0.0;
                    
                    // Remove any non-numeric characters except decimal points
                    $cleanValue = preg_replace('/[^\d.]/', '', $value);
                    return (float)$cleanValue;
                };
                
                foreach ($csvData as $rowIndex => $row) {
                    if (empty($row) || count($row) < 11) {
                        \Log::warning("Skipping row {$rowIndex}: insufficient data", $row);
                        continue;
                    }
                    
                    try {
                        // Map CSV columns to database fields
                        $affiliateId = trim($row[0]); // Column A
                        $namaAffiliate = trim($row[1]); // Column B
                        $usernameAffiliate = trim($row[2]); // Column C
                        $omzetPenjualan = $convertToInteger($row[3]); // Column D
                        $produkTerjual = $convertToInteger($row[4]); // Column E
                        $pesanan = $convertToInteger($row[5]); // Column F
                        $clicks = $convertToInteger($row[6]); // Column G
                        $estimasiKomisi = $convertToInteger($row[7]); // Column H
                        $roi = $convertToDecimal($row[8]); // Column I
                        $totalPembeli = $convertToInteger($row[9]); // Column J
                        $pembeliBaru = $convertToInteger($row[10]); // Column K
                        
                        AffiliateShopee::updateOrCreate(
                            [
                                'date' => $importDate,
                                'affiliate_id' => $affiliateId
                            ],
                            [
                                'nama_affiliate' => $namaAffiliate ?: null,
                                'username_affiliate' => $usernameAffiliate ?: null,
                                'omzet_penjualan' => $omzetPenjualan,
                                'produk_terjual' => $produkTerjual,
                                'pesanan' => $pesanan,
                                'clicks' => $clicks,
                                'estimasi_komisi' => $estimasiKomisi,
                                'roi' => $roi,
                                'total_pembeli' => $totalPembeli,
                                'pembeli_baru' => $pembeliBaru
                            ]
                        );
                        
                        $importCount++;
                        
                        \Log::info("Successfully imported row {$rowIndex}", [
                            'date' => $importDate,
                            'affiliate_id' => $affiliateId,
                            'nama_affiliate' => $namaAffiliate,
                            'omzet' => $omzetPenjualan,
                            'commission' => $estimasiKomisi,
                            'roi' => $roi
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
                    'file_name' => $file->getClientOriginalName(),
                    'import_date' => $importDate
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Affiliate Shopee data imported successfully. ' . $importCount . ' records imported for date ' . $importDate . '.'
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
     * Delete Affiliate Shopee record
     */
    public function delete_affiliate_shopee(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $affiliateShopee = AffiliateShopee::find($id);
            if (!$affiliateShopee) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Affiliate record not found'
                ], 404);
            }
            
            $affiliateShopee->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Affiliate record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 422);
        }
    }
}