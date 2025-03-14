<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\AdsMeta;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Carbon\Carbon; 
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class AdSpentSocialMediaController extends Controller
{
    public function __construct(
        protected AdSpentSocialMediaBLLInterface $adSpentSocialMediaBLL,
        protected SocialMediaBLLInterface $socialMediaBLL
    ) {}

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        $visitQuery = $this->adSpentSocialMediaBLL->getAdSpentSocialMediaDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($visitQuery)
            ->addColumn('socialMedia', function ($row) {
                return $row->socialMedia->name ?? '-';
            })
            ->addColumn('amountFormatted', function ($row) {
                return number_format($row->amount, 0, ',', '.');
            })
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.adSpentSocialMedia.index', compact('socialMedia'));
    }

    public function ads_cpas_index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);
        $kategoriProdukList = AdsMeta::select('kategori_produk')
            ->distinct()
            ->where('kategori_produk', '!=', '')
            ->pluck('kategori_produk');
            
        return view('admin.adSpentMarketPlace.ads_cpas_index', compact('kategoriProdukList'));
    }
    public function get_ads_cpas(Request $request) {
        $query = AdsMeta::query()
            ->select([
                DB::raw('date'),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value')
            ])
            ->groupBy('date');
    
        // Apply filters
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }
        
        if ($request->has('kategori_produk') && $request->kategori_produk) {
            $query->where('kategori_produk', $request->kategori_produk);
        }
    
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->date.'">'.
                       Carbon::parse($row->date)->format('d M Y').'</a>';
            })
            // Return raw values instead of formatted strings
            ->editColumn('total_amount_spent', function ($row) {
                return $row->total_amount_spent ?? 0;
            })
            ->editColumn('total_impressions', function ($row) {
                return $row->total_impressions ?? 0;
            })
            ->editColumn('total_link_clicks', function ($row) {
                return $row->total_link_clicks ?? 0;
            })
            ->editColumn('total_content_views', function ($row) {
                return $row->total_content_views ?? 0;
            })
            ->addColumn('cost_per_view', function ($row) {
                if ($row->total_content_views > 0 && $row->total_amount_spent > 0) {
                    return $row->total_amount_spent / $row->total_content_views;
                }
                return 0;
            })
            ->editColumn('total_adds_to_cart', function ($row) {
                return $row->total_adds_to_cart ?? 0;
            })
            ->addColumn('cost_per_atc', function ($row) {
                if ($row->total_adds_to_cart > 0 && $row->total_amount_spent > 0) {
                    return $row->total_amount_spent / $row->total_adds_to_cart;
                }
                return 0;
            })
            ->editColumn('total_purchases', function ($row) {
                return $row->total_purchases ?? 0;
            })
            ->addColumn('cost_per_purchase', function ($row) {
                if ($row->total_purchases > 0 && $row->total_amount_spent > 0) {
                    return $row->total_amount_spent / $row->total_purchases;
                }
                return 0;
            })
            ->editColumn('total_conversion_value', function ($row) {
                return $row->total_conversion_value ?? 0;
            })
            ->addColumn('roas', function ($row) {
                if ($row->total_amount_spent > 0 && $row->total_conversion_value > 0) {
                    return $row->total_conversion_value / $row->total_amount_spent;
                }
                return 0;
            })
            ->addColumn('cpm', function ($row) {
                if ($row->total_impressions > 0 && $row->total_amount_spent > 0) {
                    return ($row->total_amount_spent / $row->total_impressions) * 1000;
                }
                return 0;
            })
            ->addColumn('ctr', function ($row) {
                if ($row->total_impressions > 0 && $row->total_link_clicks > 0) {
                    return ($row->total_link_clicks / $row->total_impressions) * 100;
                }
                return 0;
            })
            ->addColumn('performance', function ($row) {
                if ($row->total_amount_spent > 0 && $row->total_conversion_value > 0) {
                    $roas = $row->total_conversion_value / $row->total_amount_spent;
                    
                    if ($roas >= 2.5) {
                        return '<span class="badge badge-success">Winning</span>';
                    } elseif ($roas >= 2.01) {
                        return '<span class="badge badge-primary">Bagus</span>';
                    } elseif ($roas >= 1.75) {
                        return '<span class="badge badge-info">Potensi</span>';
                    } else {
                        return '<span class="badge badge-danger">Buruk</span>';
                    }
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->rawColumns(['date', 'performance'])
            ->make(true);
    }

    public function get_ads_details_by_date(Request $request) {
        // Validate and ensure we have a date to filter by
        $date = $request->date ?? date('Y-m-d'); // Default to today if no date provided
        
        $query = AdsMeta::query()
            ->select([
                DB::raw('ANY_VALUE(id) as id'),
                DB::raw('ANY_VALUE(date) as date'), // Use ANY_VALUE for date since it's not in GROUP BY
                DB::raw('SUM(amount_spent) as amount_spent'),
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views_shared_items'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart_shared_items'),
                DB::raw('SUM(purchases_shared_items) as purchases_shared_items'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as purchases_conversion_value_shared_items'),
                'kategori_produk',
                'account_name'
            ])
            ->where('date', $date) // Filter by the specific date
            ->groupBy('account_name', 'kategori_produk');
        
        // Apply tenant filter if using multi-tenancy
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        // Apply product category filter if provided
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $query->where('kategori_produk', $request->kategori_produk);
        }
    
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('account_name', function ($row) {
                return $row->account_name ?: '-';
            })
            ->editColumn('amount_spent', function ($row) {
                return $row->amount_spent ? 'Rp ' . number_format($row->amount_spent, 0, ',', '.') : '-';
            })
            ->editColumn('impressions', function ($row) {
                return $row->impressions ? number_format($row->impressions, 0, ',', '.') : '-';
            })
            ->editColumn('link_clicks', function ($row) {
                return $row->link_clicks ? number_format($row->link_clicks, 2, ',', '.') : '-';
            })
            ->editColumn('content_views_shared_items', function ($row) {
                return $row->content_views_shared_items ? number_format($row->content_views_shared_items, 2, ',', '.') : '-';
            })
            ->addColumn('cost_per_view', function ($row) {
                if ($row->content_views_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerView = $row->amount_spent / $row->content_views_shared_items;
                    return 'Rp ' . number_format($costPerView, 2, ',', '.');
                }
                return '-';
            })
            ->editColumn('adds_to_cart_shared_items', function ($row) {
                return $row->adds_to_cart_shared_items ? number_format($row->adds_to_cart_shared_items, 2, ',', '.') : '-';
            })
            ->addColumn('cost_per_atc', function ($row) {
                if ($row->adds_to_cart_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerATC = $row->amount_spent / $row->adds_to_cart_shared_items;
                    return 'Rp ' . number_format($costPerATC, 2, ',', '.');
                }
                return '-';
            })
            ->editColumn('purchases_shared_items', function ($row) {
                return $row->purchases_shared_items ? number_format($row->purchases_shared_items, 2, ',', '.') : '-';
            })
            ->addColumn('cost_per_purchase', function ($row) {
                if ($row->purchases_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerPurchase = $row->amount_spent / $row->purchases_shared_items;
                    return 'Rp ' . number_format($costPerPurchase, 2, ',', '.');
                }
                return '-';
            })
            ->editColumn('purchases_conversion_value_shared_items', function ($row) {
                return $row->purchases_conversion_value_shared_items ? 'Rp ' . number_format($row->purchases_conversion_value_shared_items, 2, ',', '.') : '-';
            })
            ->addColumn('roas', function ($row) {
                if ($row->amount_spent > 0 && $row->purchases_conversion_value_shared_items > 0) {
                    $roas = $row->purchases_conversion_value_shared_items / $row->amount_spent;
                    return number_format($roas, 2, ',', '.');
                }
                return '-';
            })
            ->addColumn('cpm', function ($row) {
                if ($row->impressions > 0 && $row->amount_spent > 0) {
                    $cpm = ($row->amount_spent / $row->impressions) * 1000;
                    return 'Rp ' . number_format($cpm, 2, ',', '.');
                }
                return '-';
            })
            ->addColumn('ctr', function ($row) {
                if ($row->impressions > 0 && $row->link_clicks > 0) {
                    $ctr = ($row->link_clicks / $row->impressions) * 100;
                    return number_format($ctr, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('performance', function ($row) {
                if ($row->amount_spent > 0 && $row->purchases_conversion_value_shared_items > 0) {
                    $roas = $row->purchases_conversion_value_shared_items / $row->amount_spent;
                    
                    if ($roas >= 2.5) {
                        return '<span class="badge badge-success">Winning</span>';
                    } elseif ($roas >= 2.01) {
                        return '<span class="badge badge-primary">Bagus</span>';
                    } elseif ($roas >= 1.75) {
                        return '<span class="badge badge-info">Potensi</span>';
                    } else {
                        return '<span class="badge badge-danger">Bad</span>';
                    }
                }
                return '<span class="badge badge-secondary">N/A</span>';
            })
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-danger btn-sm delete-account" data-account="'.$row->account_name.'" data-date="'.$row->date.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['action', 'performance'])
            ->make(true);
    }
    public function deleteByAccountAndDate(Request $request)
    {
        try {
            $request->validate([
                'account_name' => 'required|string',
                'date' => 'required|date',
            ]);

            // Parse and format the date correctly
            $date = Carbon::parse($request->date)->format('Y-m-d');
            
            $deleted = AdsMeta::where('account_name', $request->account_name)
                ->where('date', $date) // Use the formatted date here, not $request->date
                ->where('tenant_id', auth()->user()->current_tenant_id)
                ->delete();
            
            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Account data deleted successfully for the specified date.'
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => 'No records found to delete.'
                ], 404);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error deleting data: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Create or update data Ad SpentSocial Media
     */
    public function store(AdSpentSocialMediaRequest $request): JsonResponse
    {
        $this->authorize('createAdSpentSocialMedia', AdSpentSocialMedia::class);

        $this->authorize('createSales', Sales::class);

        return response()->json($this->adSpentSocialMediaBLL->createAdSpentSocialMedia($request, Auth::user()->current_tenant_id));
    }

    /**
     * Retrieves AdSpent social media recap information based on the provided request.
     */
    public function getAdSpentRecap(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentSocialMedia', AdSpentSocialMedia::class);

        return response()->json($this->adSpentSocialMediaBLL->getAdSpentSocialMediaRecap($request, Auth::user()->current_tenant_id));
    }
    // public function import_ads(Request $request)
    // {
    //     try {
    //         $request->validate([
    //             'meta_ads_csv_file' => 'required|file|mimes:csv,txt|max:2048',
    //             'kategori_produk' => 'required|string'
    //         ]);

    //         $file = $request->file('meta_ads_csv_file');
    //         $kategoriProduk = $request->input('kategori_produk');
    //         $csvData = array_map('str_getcsv', file($file->getPathname()));
            
    //         $headers = array_shift($csvData);
    //         $groupedData = [];
    //         $adSpentData = [];
            
    //         foreach ($csvData as $row) {
    //             $date = Carbon::parse($row[0])->format('Y-m-d');
    //             $amount = (int)$row[3];
                
    //             if (!isset($groupedData[$date])) {
    //                 $groupedData[$date] = [
    //                     'amount_spent' => 0,
    //                     'impressions' => 0,
    //                     'content_views_shared_items' => 0,
    //                     'adds_to_cart_shared_items' => 0,
    //                     'purchases_shared_items' => 0,
    //                     'purchases_conversion_value_shared_items' => 0,
    //                     'link_clicks' => 0,
    //                     'kategori_produk' => $kategoriProduk
    //                 ];
    //             }
                
    //             if (!isset($adSpentData[$date])) {
    //                 $adSpentData[$date] = 0;
    //             }
    //             $groupedData[$date]['amount_spent'] += (int)$row[3];
    //             $groupedData[$date]['impressions'] += (int)$row[4];
    //             $groupedData[$date]['content_views_shared_items'] += (float)($row[5] ?? 0);
    //             $groupedData[$date]['adds_to_cart_shared_items'] += (float)($row[6] ?? 0);
    //             $groupedData[$date]['purchases_shared_items'] += (float)($row[7] ?? 0);
    //             $groupedData[$date]['purchases_conversion_value_shared_items'] += (float)($row[8] ?? 0);
    //             $groupedData[$date]['link_clicks'] += (float)($row[9] ?? 0);
                
    //             $adSpentData[$date] += $amount;
    //         }

    //         DB::beginTransaction();
    //         try {
    //             foreach ($groupedData as $date => $data) {
    //                 AdsMeta::updateOrCreate(
    //                     [
    //                         'date' => $date,
    //                         'tenant_id' => auth()->user()->current_tenant_id
    //                     ],
    //                     [
    //                         'amount_spent' => $data['amount_spent'],
    //                         'impressions' => $data['impressions'],
    //                         'content_views_shared_items' => $data['content_views_shared_items'],
    //                         'adds_to_cart_shared_items' => $data['adds_to_cart_shared_items'],
    //                         'purchases_shared_items' => $data['purchases_shared_items'],
    //                         'purchases_conversion_value_shared_items' => $data['purchases_conversion_value_shared_items'],
    //                         'link_clicks' => $data['link_clicks'],
    //                         'kategori_produk' => $data['kategori_produk']
    //                     ]
    //                 );
    //             }
    //             foreach ($adSpentData as $date => $totalAmount) {
    //                 AdSpentSocialMedia::updateOrCreate(
    //                     [
    //                         'date' => $date,
    //                         'social_media_id' => 1,
    //                         'tenant_id' => auth()->user()->current_tenant_id
    //                     ],
    //                     [
    //                         'amount' => $totalAmount
    //                     ]
    //                 );
    //             }
                
    //             DB::commit();
                
    //             return response()->json([
    //                 'status' => 'success',
    //                 'message' => 'Meta ads data imported successfully'
    //             ]);
                
    //         } catch (\Exception $e) {
    //             DB::rollBack();
    //             throw $e;
    //         }
            
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 'error',
    //             'message' => 'Error importing data: ' . $e->getMessage()
    //         ], 422);
    //     }
    // }
    

    public function import_ads(Request $request)
    {
        try {
            $request->validate([
                'meta_ads_csv_file' => 'required|file|mimes:csv,txt,zip|max:5120', // Increased size limit for ZIP
                'kategori_produk' => 'required|string'
            ]);

            $file = $request->file('meta_ads_csv_file');
            $kategoriProduk = $request->input('kategori_produk');
            $dateAmountMap = [];
            $importCount = 0;

            DB::beginTransaction();
            try {
                // Check if the file is a ZIP file
                if ($file->getClientOriginalExtension() == 'zip') {
                    // Create a temporary directory to extract files
                    $tempDir = storage_path('app/temp/') . uniqid('zip_extract_');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    
                    // Extract the ZIP file
                    $zip = new \ZipArchive;
                    if ($zip->open($file->getPathname()) === TRUE) {
                        $zip->extractTo($tempDir);
                        $zip->close();
                        
                        // Process each CSV file in the ZIP
                        $csvFiles = glob($tempDir . '/*.csv');
                        foreach ($csvFiles as $csvFile) {
                            // Pass the actual CSV filename (not the temp path)
                            $csvFilename = basename($csvFile);
                            $importCount += $this->processCsvFile($csvFile, $kategoriProduk, $dateAmountMap, $csvFilename);
                        }
                        
                        // Clean up the temporary directory
                        array_map('unlink', glob($tempDir . '/*'));
                        rmdir($tempDir);
                    } else {
                        throw new \Exception("Could not open ZIP file");
                    }
                } else {
                    // Process a single CSV file
                    // Pass the original CSV filename
                    $originalFilename = $file->getClientOriginalName();
                    $importCount = $this->processCsvFile($file->getPathname(), $kategoriProduk, $dateAmountMap, $originalFilename);
                }

                // Update AdSpentSocialMedia with aggregated totals
                foreach ($dateAmountMap as $date => $totalAmount) {
                    AdSpentSocialMedia::updateOrCreate(
                        [
                            'date' => $date,
                            'social_media_id' => 1,
                            'tenant_id' => auth()->user()->current_tenant_id
                        ],
                        [
                            'amount' => $totalAmount
                        ]
                    );
                }
                
                DB::commit();
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'Meta ads data imported successfully. ' . $importCount . ' records imported.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }

/**
 * Process a single CSV file and import its data
 * 
 * @param string $filePath Path to the CSV file
 * @param string $kategoriProduk Product category
 * @param array &$dateAmountMap Reference to date-amount mapping for aggregation
 * @param string|null $originalFilename Original filename of the CSV
 * @return int Number of records imported
 */
private function processCsvFile($filePath, $kategoriProduk, &$dateAmountMap, $originalFilename = null)
{
    $csvData = array_map('str_getcsv', file($filePath));
    $headers = array_shift($csvData);
    $count = 0;
    
    // Use the provided original filename instead of the temporary path
    $filename = $originalFilename ?? basename($filePath);
    
    // Remove file extension first if it exists
    $filename = pathinfo($filename, PATHINFO_FILENAME);
    
    // Remove everything from "-Campaign" onwards
    if (strpos($filename, '-Campaign') !== false) {
        $accountName = substr($filename, 0, strpos($filename, '-Campaign'));
    } else {
        // Fallback - use the whole filename as account name
        $accountName = $filename;
    }
    
    foreach ($csvData as $row) {
        // Skip empty rows
        if (empty($row[0])) {
            continue;
        }
        
        try {
            $date = Carbon::parse($row[0])->format('Y-m-d');
            $amount = (int)$row[3];
            $campaignName = $row[2] ?? null;
            
            // Check for existing record and update if found, create if not
            AdsMeta::updateOrCreate(
                [
                    'date' => $date,
                    'campaign_name' => $campaignName,
                    'kategori_produk' => $kategoriProduk,
                    'tenant_id' => Auth::user()->current_tenant_id
                ],
                [
                    'amount_spent' => (int)$row[3],
                    'impressions' => (int)$row[4],
                    'content_views_shared_items' => (float)($row[5] ?? 0),
                    'adds_to_cart_shared_items' => (float)($row[6] ?? 0),
                    'purchases_shared_items' => (float)($row[7] ?? 0),
                    'purchases_conversion_value_shared_items' => (float)($row[8] ?? 0),
                    'link_clicks' => (float)($row[9] ?? 0),
                    'account_name' => $accountName
                ]
            );
            
            if (!isset($dateAmountMap[$date])) {
                $dateAmountMap[$date] = 0;
            }
            $dateAmountMap[$date] += $amount;
            
            $count++;
        } catch (\Exception $e) {
            // Log the error but continue processing other rows
            \Log::warning("Error processing row in CSV: " . json_encode($row) . " - " . $e->getMessage());
        }
    }
    
    return $count;
}
    
    public function getFunnelData(Request $request)
    {
        try {
            // Check if filterDates is set and has the expected format
            if (!$request->has('filterDates') || !$request->filterDates) {
                // Use default date range (e.g., this month)
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            } else {
                // Try to parse the date range
                try {
                    $dates = explode(' - ', $request->filterDates);
                    
                    // Make sure we have two date parts
                    if (count($dates) != 2) {
                        throw new \Exception("Invalid date range format");
                    }
                    
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]));
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]));
                } catch (\Exception $e) {
                    // Log the actual input for debugging
                    \Log::error("Date parse error with input: " . $request->filterDates);
                    throw new \Exception("Invalid date format. Expected format: DD/MM/YYYY - DD/MM/YYYY");
                }
            }

            // Build the query
            $query = AdsMeta::select(
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases')
            )
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            // Apply product category filter if provided
            if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            $data = $query->first();

            return response()->json([
                'status' => 'success',
                'data' => [
                    [
                        'name' => 'Impressions',
                        'value' => (int)($data->total_impressions ?? 0)
                    ],
                    [
                        'name' => 'Content Views',
                        'value' => (int)($data->total_content_views ?? 0)
                    ],
                    [
                        'name' => 'Adds to Cart',
                        'value' => (int)($data->total_adds_to_cart ?? 0)
                    ],
                    [
                        'name' => 'Purchases',
                        'value' => (int)($data->total_purchases ?? 0)
                    ]
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching funnel data: ' . $e->getMessage()
            ], 422);
        }
    }
    public function getImpressionChartData(Request $request)
    {
        try {
            // Check if filterDates is set and has the expected format
            if (!$request->has('filterDates') || !$request->filterDates) {
                // Use default date range (e.g., this month)
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
            } else {
                // Try to parse the date range
                try {
                    $dates = explode(' - ', $request->filterDates);
                    
                    // Make sure we have two date parts
                    if (count($dates) != 2) {
                        throw new \Exception("Invalid date range format");
                    }
                    
                    $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]));
                    $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]));
                } catch (\Exception $e) {
                    // Log the actual input for debugging
                    \Log::error("Date parse error with input: " . $request->filterDates);
                    throw new \Exception("Invalid date format. Expected format: DD/MM/YYYY - DD/MM/YYYY");
                }
            }

            // Log the date range for debugging
            \Log::info("Impression data filter dates: " . $startDate->format('Y-m-d') . " to " . $endDate->format('Y-m-d'));

            // Apply kategori_produk filter if provided
            $query = AdsMeta::select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->where('tenant_id', auth()->user()->current_tenant_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            // Apply product category filter if provided
            if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            $data = $query->groupBy('date')
                ->orderBy('date')
                ->get()
                ->map(function ($item) {
                    return [
                        'date' => Carbon::parse($item->date)->format('Y-m-d'),
                        'impressions' => (int)$item->impressions
                    ];
                });

            return response()->json([
                'status' => 'success',
                'impressions' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error fetching impression data: ' . $e->getMessage()
            ], 422);
        }
    }
}
