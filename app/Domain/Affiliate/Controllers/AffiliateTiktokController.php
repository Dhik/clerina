<?php

namespace App\Domain\Affiliate\Controllers;

use App\Domain\Affiliate\Models\AffiliateTiktok;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Domain\User\Models\User;
use Carbon\Carbon;
use App\Domain\Sales\Models\SalesChannel;
use Auth;
use DB;
use Log;
use PhpOffice\PhpSpreadsheet\IOFactory;

class AffiliateTiktokController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Get distinct creators for filter dropdown
        $creatorList = AffiliateTiktok::select('creator_username')
            ->distinct()
            ->where('creator_username', '!=', '')
            ->pluck('creator_username');
        
        return view('admin.affiliate_tiktok.index', compact('creatorList'));
    }

    /**
     * Get Affiliate TikTok data for DataTables
     */
    public function get_affiliate_tiktok(Request $request) 
    {
        $query = AffiliateTiktok::query()
            ->select([
                DB::raw('date'),
                DB::raw('COUNT(*) as total_creators'),
                DB::raw('SUM(affiliate_gmv) as total_gmv'),
                DB::raw('SUM(affiliate_live_gmv) as total_live_gmv'),
                DB::raw('SUM(affiliate_shoppable_video) as total_shoppable_video'),
                DB::raw('SUM(affiliate_product_card_gmv) as total_product_card_gmv'),
                DB::raw('SUM(affiliate_products_sold) as total_products_sold'),
                DB::raw('SUM(items_sold) as total_items_sold'),
                DB::raw('SUM(est_commission) as total_commission'),
                DB::raw('AVG(avg_order_value) as avg_order_value'),
                DB::raw('SUM(affiliate_orders) as total_orders'),
                DB::raw('SUM(product_impressions) as total_impressions'),
                DB::raw('SUM(affiliate_live_streams) as total_live_streams'),
                DB::raw('SUM(open_collaboration_gmv) as total_open_collab_gmv'),
                DB::raw('SUM(open_collaboration_est) as total_open_collab_est'),
                DB::raw('SUM(affiliate_refunded_gmv) as total_refunded_gmv'),
                DB::raw('SUM(affiliate_items_refunded) as total_items_refunded'),
                DB::raw('SUM(affiliate_followers) as total_followers')
            ])
            ->groupBy('date');

        // Apply filters
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }
        
        if ($request->has('creator_username') && $request->creator_username) {
            $query->where('creator_username', $request->creator_username);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->date.'">'.
                       Carbon::parse($row->date)->format('d M Y').'</a>';
            })
            ->editColumn('total_creators', function ($row) {
                return $row->total_creators ?? 0;
            })
            ->editColumn('total_gmv', function ($row) {
                return $row->total_gmv ? 'Rp ' . number_format($row->total_gmv, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_live_gmv', function ($row) {
                return $row->total_live_gmv ? 'Rp ' . number_format($row->total_live_gmv, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_products_sold', function ($row) {
                return $row->total_products_sold ?? 0;
            })
            ->editColumn('total_items_sold', function ($row) {
                return $row->total_items_sold ?? 0;
            })
            ->editColumn('total_commission', function ($row) {
                return $row->total_commission ? 'Rp ' . number_format($row->total_commission, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('avg_order_value', function ($row) {
                return $row->avg_order_value ? 'Rp ' . number_format($row->avg_order_value, 0, ',', '.') : 'Rp 0';
            })
            ->editColumn('total_orders', function ($row) {
                return $row->total_orders ?? 0;
            })
            ->editColumn('total_impressions', function ($row) {
                return $row->total_impressions ?? 0;
            })
            ->editColumn('total_live_streams', function ($row) {
                return $row->total_live_streams ?? 0;
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->total_impressions > 0 && $row->total_orders > 0) {
                    return number_format(($row->total_orders / $row->total_impressions) * 100, 2) . '%';
                }
                return '0%';
            })
            ->addColumn('avg_commission_per_creator', function ($row) {
                if ($row->total_creators > 0 && $row->total_commission > 0) {
                    return 'Rp ' . number_format($row->total_commission / $row->total_creators, 0, ',', '.');
                }
                return 'Rp 0';
            })
            ->addColumn('performance', function ($row) {
                // Performance based on conversion rate
                if ($row->total_impressions > 0 && $row->total_orders > 0) {
                    $conversionRate = ($row->total_orders / $row->total_impressions) * 100;
                    
                    if ($conversionRate >= 2) {
                        return '<span class="badge badge-success">Excellent</span>';
                    } elseif ($conversionRate >= 1) {
                        return '<span class="badge badge-primary">Good</span>';
                    } elseif ($conversionRate >= 0.5) {
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
     * Get Affiliate TikTok details by date
     */
    public function get_affiliate_tiktok_details_by_date(Request $request) 
    {
        $query = AffiliateTiktok::query()
            ->select([
                'id',
                'date',
                'creator_username',
                'affiliate_gmv',
                'affiliate_live_gmv',
                'affiliate_shoppable_video',
                'affiliate_product_card_gmv',
                'affiliate_products_sold',
                'items_sold',
                'est_commission',
                'avg_order_value',
                'affiliate_orders',
                'ctr',
                'product_impressions',
                'avg_affiliate_customers',
                'affiliate_live_streams',
                'open_collaboration_gmv',
                'open_collaboration_est',
                'affiliate_refunded_gmv',
                'affiliate_items_refunded',
                'affiliate_followers'
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

        if ($request->has('creator_username') && $request->creator_username !== '') {
            $query->where('creator_username', $request->creator_username);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('affiliate_gmv', function ($row) {
                return $row->affiliate_gmv ? 'Rp ' . number_format($row->affiliate_gmv, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_live_gmv', function ($row) {
                return $row->affiliate_live_gmv ? 'Rp ' . number_format($row->affiliate_live_gmv, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_shoppable_video', function ($row) {
                return $row->affiliate_shoppable_video ? 'Rp ' . number_format($row->affiliate_shoppable_video, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_product_card_gmv', function ($row) {
                return $row->affiliate_product_card_gmv ? 'Rp ' . number_format($row->affiliate_product_card_gmv, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_products_sold', function ($row) {
                return $row->affiliate_products_sold ? number_format($row->affiliate_products_sold, 0, ',', '.') : '-';
            })
            ->editColumn('items_sold', function ($row) {
                return $row->items_sold ? number_format($row->items_sold, 0, ',', '.') : '-';
            })
            ->editColumn('est_commission', function ($row) {
                return $row->est_commission ? 'Rp ' . number_format($row->est_commission, 0, ',', '.') : '-';
            })
            ->editColumn('avg_order_value', function ($row) {
                return $row->avg_order_value ? 'Rp ' . number_format($row->avg_order_value, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_orders', function ($row) {
                return $row->affiliate_orders ? number_format($row->affiliate_orders, 0, ',', '.') : '-';
            })
            ->editColumn('ctr', function ($row) {
                return $row->ctr ? $row->ctr : '-';
            })
            ->editColumn('product_impressions', function ($row) {
                return $row->product_impressions ? number_format($row->product_impressions, 0, ',', '.') : '-';
            })
            ->editColumn('avg_affiliate_customers', function ($row) {
                return $row->avg_affiliate_customers ? number_format($row->avg_affiliate_customers, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_live_streams', function ($row) {
                return $row->affiliate_live_streams ? number_format($row->affiliate_live_streams, 0, ',', '.') : '-';
            })
            ->editColumn('open_collaboration_gmv', function ($row) {
                return $row->open_collaboration_gmv ? 'Rp ' . number_format($row->open_collaboration_gmv, 0, ',', '.') : '-';
            })
            ->editColumn('open_collaboration_est', function ($row) {
                return $row->open_collaboration_est ? 'Rp ' . number_format($row->open_collaboration_est, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_refunded_gmv', function ($row) {
                return $row->affiliate_refunded_gmv ? 'Rp ' . number_format($row->affiliate_refunded_gmv, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_items_refunded', function ($row) {
                return $row->affiliate_items_refunded ? number_format($row->affiliate_items_refunded, 0, ',', '.') : '-';
            })
            ->editColumn('affiliate_followers', function ($row) {
                return $row->affiliate_followers ? number_format($row->affiliate_followers, 0, ',', '.') : '-';
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->product_impressions > 0 && $row->affiliate_orders > 0) {
                    $rate = ($row->affiliate_orders / $row->product_impressions) * 100;
                    return number_format($rate, 4, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('commission_rate', function ($row) {
                if ($row->affiliate_gmv > 0 && $row->est_commission > 0) {
                    $rate = ($row->est_commission / $row->affiliate_gmv) * 100;
                    return number_format($rate, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('refund_rate', function ($row) {
                if ($row->affiliate_gmv > 0 && $row->affiliate_refunded_gmv > 0) {
                    $rate = ($row->affiliate_refunded_gmv / $row->affiliate_gmv) * 100;
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
     * Get line chart data for GMV over time
     */
    public function get_line_data(Request $request)
    {
        try {
            $query = AffiliateTiktok::query();
            
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
            
            // Apply creator filter
            if ($request->has('creator_username') && $request->creator_username) {
                $query->where('creator_username', $request->creator_username);
                \Log::info('Line chart creator filter applied', ['creator_username' => $request->creator_username]);
            }
            
            // Debug: Get total count before grouping
            $totalRecords = AffiliateTiktok::count();
            $filteredCount = (clone $query)->count();
            
            \Log::info('Line chart query debug', [
                'total_records_in_table' => $totalRecords,
                'filtered_records_count' => $filteredCount,
                'has_filter_dates' => $request->has('filterDates'),
                'filter_dates_value' => $request->filterDates,
                'has_creator_filter' => $request->has('creator_username'),
                'creator_filter_value' => $request->creator_username
            ]);
            
            // Group by date and get sum of GMV
            $gmvData = $query->select(
                'date',
                DB::raw('SUM(affiliate_gmv) as total_gmv')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'gmv' => (int)$item->total_gmv
                ];
            });
            
            \Log::info('Line chart data result', [
                'data_count' => $gmvData->count(),
                'data' => $gmvData->toArray()
            ]);
            
            // If no data, return empty array
            if ($gmvData->isEmpty()) {
                $gmvData = collect([]);
            }
            
            return response()->json([
                'status' => 'success',
                'gmv' => $gmvData,
                'has_data' => $gmvData->isNotEmpty(),
                'debug' => [
                    'total_records' => $totalRecords,
                    'filtered_count' => $filteredCount,
                    'result_count' => $gmvData->count()
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
     * Get funnel data for TikTok affiliate analysis
     */
    public function get_funnel_data(Request $request)
    {
        try {
            $query = AffiliateTiktok::query();
            
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
            
            // Apply creator filter
            if ($request->has('creator_username') && $request->creator_username) {
                $query->where('creator_username', $request->creator_username);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(product_impressions) as total_impressions'),
                DB::raw('SUM(affiliate_followers) as total_followers'),
                DB::raw('SUM(affiliate_orders) as total_orders'),
                DB::raw('SUM(affiliate_products_sold) as total_products_sold'),
                DB::raw('SUM(est_commission) as total_commission')
            )->first();
            
            // Handle empty data case
            if (!$aggregates || $aggregates->total_impressions == 0) {
                $funnelData = [
                    ['name' => 'Product Impressions', 'value' => 0],
                    ['name' => 'Total Followers', 'value' => 0],
                    ['name' => 'Affiliate Orders', 'value' => 0],
                    ['name' => 'Products Sold', 'value' => 0],
                    ['name' => 'Commission (K)', 'value' => 0]
                ];
            } else {
                // Prepare data for funnel chart
                $funnelData = [
                    ['name' => 'Product Impressions', 'value' => (int)$aggregates->total_impressions],
                    ['name' => 'Total Followers', 'value' => (int)$aggregates->total_followers],
                    ['name' => 'Affiliate Orders', 'value' => (int)$aggregates->total_orders],
                    ['name' => 'Products Sold', 'value' => (int)$aggregates->total_products_sold],
                    ['name' => 'Commission (K)', 'value' => (int)($aggregates->total_commission / 1000)]
                ];
            }
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData,
                'has_data' => $aggregates && $aggregates->total_impressions > 0
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Extract date from filename
     * Example: Creator_List_20250601-20250601_20250619014737_Shop_Tokopedia (1).xlsx -> 2025-06-01
     */
    private function extractDateFromFilename($filename)
    {
        // Try to match the pattern: filename_YYYYMMDD-YYYYMMDD_timestamp_
        if (preg_match('/(\d{8})-\d{8}/', $filename, $matches)) {
            $dateString = $matches[1]; // Get the first date (20250601)
            
            try {
                // Parse the date string and format it as Y-m-d
                $date = Carbon::createFromFormat('Ymd', $dateString);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::error('Error parsing date from filename: ' . $e->getMessage(), [
                    'filename' => $filename,
                    'dateString' => $dateString
                ]);
            }
        }
        
        // If pattern doesn't match, try alternative patterns
        if (preg_match('/(\d{4})(\d{2})(\d{2})/', $filename, $matches)) {
            try {
                $year = $matches[1];
                $month = $matches[2];
                $day = $matches[3];
                
                $date = Carbon::createFromFormat('Y-m-d', "$year-$month-$day");
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                \Log::error('Error parsing alternative date pattern from filename: ' . $e->getMessage(), [
                    'filename' => $filename
                ]);
            }
        }
        
        // If no date found in filename, return today's date as fallback
        \Log::warning('Could not extract date from filename, using today as fallback', [
            'filename' => $filename
        ]);
        return Carbon::now()->format('Y-m-d');
    }

    /**
     * Import Affiliate TikTok data from Excel file
     */
    public function import_affiliate_tiktok(Request $request)
    {
        try {
            $request->validate([
                'affiliate_tiktok_excel_file' => 'required|file|mimes:xlsx,xls|max:10240' // 10MB max
            ]);

            $file = $request->file('affiliate_tiktok_excel_file');
            $originalFilename = $file->getClientOriginalName();
            
            // Extract date from filename
            $importDate = $this->extractDateFromFilename($originalFilename);
            $importCount = 0;

            DB::beginTransaction();
            try {
                // Load the Excel file
                $spreadsheet = IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
                
                // Remove header row if exists
                if (!empty($rows)) {
                    $headers = array_shift($rows);
                    \Log::info('Excel Headers:', $headers);
                }
                
                // Helper function to convert number format to integer
                $convertToInteger = function($value) {
                    if (empty($value) || $value === null) return 0;
                    
                    // Handle numeric values
                    if (is_numeric($value)) {
                        return (int)$value;
                    }
                    
                    // Remove any non-numeric characters except decimal points
                    $cleanValue = preg_replace('/[^\d.]/', '', $value);
                    return (int)$cleanValue;
                };
                
                foreach ($rows as $rowIndex => $row) {
                    if (empty($row) || count($row) < 19) {
                        \Log::warning("Skipping row {$rowIndex}: insufficient data", $row);
                        continue;
                    }
                    
                    try {
                        // Map Excel columns to database fields according to the specification
                        $creatorUsername = trim($row[0] ?? ''); // Column A
                        $affiliateGmv = $convertToInteger($row[1] ?? 0); // Column B
                        $affiliateLiveGmv = $convertToInteger($row[2] ?? 0); // Column C
                        $affiliateShoppableVideo = $convertToInteger($row[3] ?? 0); // Column D
                        $affiliateProductCardGmv = $convertToInteger($row[4] ?? 0); // Column E
                        $affiliateProductsSold = $convertToInteger($row[5] ?? 0); // Column F
                        $itemsSold = $convertToInteger($row[6] ?? 0); // Column G
                        $estCommission = $convertToInteger($row[7] ?? 0); // Column H
                        $avgOrderValue = $convertToInteger($row[8] ?? 0); // Column I
                        $affiliateOrders = $convertToInteger($row[9] ?? 0); // Column J
                        $ctr = trim($row[10] ?? ''); // Column K
                        $productImpressions = $convertToInteger($row[11] ?? 0); // Column L
                        $avgAffiliateCustomers = $convertToInteger($row[12] ?? 0); // Column M
                        $affiliateLiveStreams = $convertToInteger($row[13] ?? 0); // Column N
                        $openCollaborationGmv = $convertToInteger($row[14] ?? 0); // Column O
                        $openCollaborationEst = $convertToInteger($row[15] ?? 0); // Column P
                        $affiliateRefundedGmv = $convertToInteger($row[16] ?? 0); // Column Q
                        $affiliateItemsRefunded = $convertToInteger($row[17] ?? 0); // Column R
                        $affiliateFollowers = $convertToInteger($row[18] ?? 0); // Column S
                        
                        // Skip empty rows
                        if (empty($creatorUsername)) {
                            continue;
                        }
                        
                        AffiliateTiktok::updateOrCreate(
                            [
                                'date' => $importDate,
                                'creator_username' => $creatorUsername
                            ],
                            [
                                'affiliate_gmv' => $affiliateGmv,
                                'affiliate_live_gmv' => $affiliateLiveGmv,
                                'affiliate_shoppable_video' => $affiliateShoppableVideo,
                                'affiliate_product_card_gmv' => $affiliateProductCardGmv,
                                'affiliate_products_sold' => $affiliateProductsSold,
                                'items_sold' => $itemsSold,
                                'est_commission' => $estCommission,
                                'avg_order_value' => $avgOrderValue,
                                'affiliate_orders' => $affiliateOrders,
                                'ctr' => $ctr ?: null,
                                'product_impressions' => $productImpressions,
                                'avg_affiliate_customers' => $avgAffiliateCustomers,
                                'affiliate_live_streams' => $affiliateLiveStreams,
                                'open_collaboration_gmv' => $openCollaborationGmv,
                                'open_collaboration_est' => $openCollaborationEst,
                                'affiliate_refunded_gmv' => $affiliateRefundedGmv,
                                'affiliate_items_refunded' => $affiliateItemsRefunded,
                                'affiliate_followers' => $affiliateFollowers
                            ]
                        );
                        
                        $importCount++;
                        
                        \Log::info("Successfully imported row {$rowIndex}", [
                            'date' => $importDate,
                            'creator_username' => $creatorUsername,
                            'affiliate_gmv' => $affiliateGmv,
                            'est_commission' => $estCommission,
                            'affiliate_orders' => $affiliateOrders
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
                    'file_name' => $originalFilename,
                    'extracted_date' => $importDate
                ]);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Affiliate TikTok data imported successfully. ' . $importCount . ' records imported for date ' . $importDate . '. (Date extracted from filename: ' . $originalFilename . ')'
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
     * Delete Affiliate TikTok record
     */
    public function delete_affiliate_tiktok(Request $request)
    {
        try {
            $id = $request->input('id');
            
            $affiliateTiktok = AffiliateTiktok::find($id);
            if (!$affiliateTiktok) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Affiliate TikTok record not found'
                ], 404);
            }
            
            $affiliateTiktok->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Affiliate TikTok record deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting record: ' . $e->getMessage()
            ], 422);
        }
    }
}