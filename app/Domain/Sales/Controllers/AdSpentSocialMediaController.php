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
        
        $picList = AdsMeta::select('pic')
            ->distinct()
            ->where('pic', '!=', '')
            ->pluck('pic');
            
        return view('admin.adSpentMarketPlace.ads_cpas_index', compact('kategoriProdukList', 'picList'));
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

        if ($request->has('pic') && $request->pic) {
            $query->where('pic', $request->pic);
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

    public function get_ads_details_by_date(Request $request) 
    {
        $query = AdsMeta::query()
            ->select([
                DB::raw('ANY_VALUE(id) as id'), // Using ANY_VALUE for ONLY_FULL_GROUP_BY mode
                DB::raw('MIN(date) as start_date'), // Get the earliest date in the range
                DB::raw('MAX(date) as end_date'), // Get the latest date in the range
                DB::raw('SUM(amount_spent) as amount_spent'),
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views_shared_items'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart_shared_items'),
                DB::raw('SUM(purchases_shared_items) as purchases_shared_items'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as purchases_conversion_value_shared_items'),
                // Add TOFU/MOFU/BOFU calculations
                DB::raw('SUM(CASE WHEN account_name LIKE "%TOFU%" THEN amount_spent ELSE 0 END) as tofu_spent'),
                DB::raw('SUM(CASE WHEN account_name LIKE "%MOFU%" THEN amount_spent ELSE 0 END) as mofu_spent'),
                DB::raw('SUM(CASE WHEN account_name LIKE "%BOFU%" THEN amount_spent ELSE 0 END) as bofu_spent'),
                'kategori_produk',
                'account_name'
            ]);
        
        // Apply date filter with support for both single date and date range
        if ($request->has('date_start') && $request->has('date_end')) {
            // Handle date range
            try {
                $dateStart = Carbon::parse($request->input('date_start'))->format('Y-m-d');
                $dateEnd = Carbon::parse($request->input('date_end'))->format('Y-m-d');
                $query->whereBetween('date', [$dateStart, $dateEnd]);
            } catch (\Exception $e) {
                // If date parsing fails, log the error
                Log::error('Date parsing error: ' . $e->getMessage());
            }
        } elseif ($request->has('date')) {
            // Handle single date (existing functionality)
            try {
                $parsedDate = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('date', $parsedDate);
            } catch (\Exception $e) {
                // If date parsing fails, try to use it as is
                $query->where('date', $request->input('date'));
            }
        }
        
        // Changed grouping to only include account_name and kategori_produk
        $query->groupBy('account_name', 'kategori_produk');
        
        // Apply tenant filter if using multi-tenancy
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        // Apply product category filter if provided
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $query->where('kategori_produk', $request->kategori_produk);
        }
        
        // Apply PIC filter if provided
        if ($request->has('pic') && $request->pic !== '') {
            $query->where('pic', $request->pic);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('account_name', function ($row) {
                return $row->account_name ?: '-';
            })
            ->editColumn('date', function ($row) {
                // Show date range instead of single date
                if ($row->start_date == $row->end_date) {
                    return $row->start_date;
                }
                return $row->start_date . ' to ' . $row->end_date;
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
            // Add TOFU spent column
            ->addColumn('tofu_spent', function ($row) {
                return $row->tofu_spent ? 'Rp ' . number_format($row->tofu_spent, 0, ',', '.') : '-';
            })
            // Add TOFU percentage column
            ->addColumn('tofu_percentage', function ($row) {
                if ($row->amount_spent > 0 && $row->tofu_spent > 0) {
                    $percentage = ($row->tofu_spent / $row->amount_spent) * 100;
                    return number_format($percentage, 2, ',', '.') . '%';
                }
                return '-';
            })
            // Add MOFU spent column
            ->addColumn('mofu_spent', function ($row) {
                return $row->mofu_spent ? 'Rp ' . number_format($row->mofu_spent, 0, ',', '.') : '-';
            })
            // Add MOFU percentage column
            ->addColumn('mofu_percentage', function ($row) {
                if ($row->amount_spent > 0 && $row->mofu_spent > 0) {
                    $percentage = ($row->mofu_spent / $row->amount_spent) * 100;
                    return number_format($percentage, 2, ',', '.') . '%';
                }
                return '-';
            })
            // Add BOFU spent column
            ->addColumn('bofu_spent', function ($row) {
                return $row->bofu_spent ? 'Rp ' . number_format($row->bofu_spent, 0, ',', '.') : '-';
            })
            // Add BOFU percentage column
            ->addColumn('bofu_percentage', function ($row) {
                if ($row->amount_spent > 0 && $row->bofu_spent > 0) {
                    $percentage = ($row->bofu_spent / $row->amount_spent) * 100;
                    return number_format($percentage, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('cost_per_view', function ($row) {
                if ($row->content_views_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerView = $row->amount_spent / $row->content_views_shared_items;
                    return 'Rp ' . number_format(floor($costPerView), 0, ',', '.');
                }
                return '-';
            })
            ->editColumn('adds_to_cart_shared_items', function ($row) {
                return $row->adds_to_cart_shared_items ? number_format(floor($row->adds_to_cart_shared_items), 0, ',', '.') : '-';
            })
            ->addColumn('cost_per_atc', function ($row) {
                if ($row->adds_to_cart_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerATC = $row->amount_spent / $row->adds_to_cart_shared_items;
                    return 'Rp ' . number_format(floor($costPerATC), 0, ',', '.');
                }
                return '-';
            })
            ->editColumn('purchases_shared_items', function ($row) {
                return $row->purchases_shared_items ? number_format($row->purchases_shared_items, 2, ',', '.') : '-';
            })
            ->addColumn('cost_per_purchase', function ($row) {
                if ($row->purchases_shared_items > 0 && $row->amount_spent > 0) {
                    $costPerPurchase = $row->amount_spent / $row->purchases_shared_items;
                    return 'Rp ' . number_format(floor($costPerPurchase), 0, ',', '.');
                }
                return '-';
            })
            ->editColumn('purchases_conversion_value_shared_items', function ($row) {
                return $row->purchases_conversion_value_shared_items ? 'Rp ' . number_format($row->purchases_conversion_value_shared_items, 0, ',', '.') : '-';
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
                    return 'Rp ' . number_format(floor($cpm), 0, ',', '.');
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
                return '<button type="button" class="btn btn-danger btn-sm delete-account" data-account="'.$row->account_name.'" data-kategori="'.$row->kategori_produk.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['action', 'performance'])
            ->make(true);
    }

    public function get_campaign_summary(Request $request)
    {
        $query = AdsMeta::query()
            ->select([
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(DISTINCT account_name) as accounts_count'),
                
                // New TOFU/MOFU/BOFU metrics
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%TOFU%" THEN amount_spent ELSE 0 END) as tofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%MOFU%" THEN amount_spent ELSE 0 END) as mofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%BOFU%" THEN amount_spent ELSE 0 END) as bofu_spent')
            ]);
        
        // Apply date filter with support for both single date and date range
        if ($request->has('date_start') && $request->has('date_end')) {
            // Handle date range
            try {
                $dateStart = Carbon::parse($request->input('date_start'))->format('Y-m-d');
                $dateEnd = Carbon::parse($request->input('date_end'))->format('Y-m-d');
                $query->whereBetween('date', [$dateStart, $dateEnd]);
            } catch (\Exception $e) {
                // If date parsing fails, log the error
                Log::error('Date parsing error: ' . $e->getMessage());
            }
        } elseif ($request->has('date')) {
            // Handle single date
            try {
                $parsedDate = Carbon::parse($request->input('date'))->format('Y-m-d');
                $query->where('date', $parsedDate);
            } catch (\Exception $e) {
                // If date parsing fails, try to use it as is
                $query->where('date', $request->input('date'));
            }
        }
        
        // Apply product category filter if provided
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $query->where('kategori_produk', $request->kategori_produk);
        }
        
        // Apply PIC filter if provided
        if ($request->has('pic') && $request->pic !== '') {
            $query->where('pic', $request->pic);
        }
        
        $summary = $query->first();
        
        // Calculate derived metrics
        $result = [
            'total_amount_spent' => $summary->total_amount_spent,
            'total_impressions' => $summary->total_impressions,
            'total_link_clicks' => $summary->total_link_clicks,
            'total_content_views' => $summary->total_content_views,
            'total_adds_to_cart' => $summary->total_adds_to_cart,
            'total_purchases' => $summary->total_purchases,
            'total_conversion_value' => $summary->total_conversion_value,
            'accounts_count' => $summary->accounts_count,
            
            // Derived metrics
            'cost_per_purchase' => $summary->total_purchases > 0 ? $summary->total_amount_spent / $summary->total_purchases : 0,
            'cost_per_view' => $summary->total_content_views > 0 ? $summary->total_amount_spent / $summary->total_content_views : 0,
            'cost_per_atc' => $summary->total_adds_to_cart > 0 ? $summary->total_amount_spent / $summary->total_adds_to_cart : 0,
            'cpm' => $summary->total_impressions > 0 ? ($summary->total_amount_spent / $summary->total_impressions) * 1000 : 0,
            'ctr' => $summary->total_impressions > 0 ? ($summary->total_link_clicks / $summary->total_impressions) * 100 : 0,
            'roas' => $summary->total_amount_spent > 0 ? $summary->total_conversion_value / $summary->total_amount_spent : 0,
            
            // New funnel stage metrics with percentages
            'tofu_spent' => $summary->tofu_spent,
            'mofu_spent' => $summary->mofu_spent, 
            'bofu_spent' => $summary->bofu_spent,
            'tofu_percentage' => $summary->total_amount_spent > 0 ? ($summary->tofu_spent / $summary->total_amount_spent) * 100 : 0,
            'mofu_percentage' => $summary->total_amount_spent > 0 ? ($summary->mofu_spent / $summary->total_amount_spent) * 100 : 0,
            'bofu_percentage' => $summary->total_amount_spent > 0 ? ($summary->bofu_spent / $summary->total_amount_spent) * 100 : 0
        ];
        
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
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
        
        $filename = $originalFilename ?? basename($filePath);
        
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        
        if (strpos($filename, '-Campaign') !== false) {
            $accountName = substr($filename, 0, strpos($filename, '-Campaign'));
        } else {
            $accountName = $filename;
        }
        
        $pic = $this->determinePIC($accountName);
        
        foreach ($csvData as $row) {
            if (empty($row[0])) {
                continue;
            }
            
            try {
                $date = Carbon::parse($row[0])->format('Y-m-d');
                $amount = (int)$row[3];
                $campaignName = $row[2] ?? null;
                
                $newCreated = null;
                $lastUpdated = null;
                
                if (isset($row[10]) && !empty($row[10])) {
                    try {
                        $newCreated = Carbon::parse($row[10])->format('Y-m-d');
                    } catch (\Exception $e) {
                        \Log::warning("Invalid new_created date format in CSV: " . $row[10]);
                    }
                }
                
                if (isset($row[11]) && !empty($row[11])) {
                    try {
                        $lastUpdated = Carbon::parse($row[11])->format('Y-m-d');
                    } catch (\Exception $e) {
                        \Log::warning("Invalid last_updated date format in CSV: " . $row[11]);
                    }
                }
                
                AdsMeta::updateOrCreate(
                    [
                        'date' => $date,
                        'campaign_name' => $campaignName,
                        'amount_spent' => $amount,
                        'kategori_produk' => $kategoriProduk,
                        'tenant_id' => Auth::user()->current_tenant_id
                    ],
                    [
                        'impressions' => (int)$row[4],
                        'content_views_shared_items' => (float)($row[5] ?? 0),
                        'adds_to_cart_shared_items' => (float)($row[6] ?? 0),
                        'purchases_shared_items' => (float)($row[7] ?? 0),
                        'purchases_conversion_value_shared_items' => (float)($row[8] ?? 0),
                        'link_clicks' => (float)($row[9] ?? 0),
                        'account_name' => $accountName,
                        'pic' => $pic,
                        'new_created' => $newCreated,
                        'last_updated' => $lastUpdated
                    ]
                );
                
                if (!isset($dateAmountMap[$date])) {
                    $dateAmountMap[$date] = 0;
                }
                $dateAmountMap[$date] += $amount;
                
                $count++;
            } catch (\Exception $e) {
                \Log::warning("Error processing row in CSV: " . json_encode($row) . " - " . $e->getMessage());
            }
        }
        
        return $count;
    }
        
    /**
     * Determine the PIC based on account name patterns
     * 
     * @param string $accountName The account name to analyze
     * @return string The determined PIC
     */
    private function determinePIC($accountName)
    {
        // Regular expressions for matching account names
        if (preg_match('/---REZA(-|---|$|\s|&)/i', $accountName) || 
            preg_match('/REZA---/i', $accountName) ||
            preg_match('/---JB---REZA/i', $accountName) ||
            preg_match('/---GS---REZA/i', $accountName) ||
            preg_match('/CPAS-CLEORA-JAKARTA/i', $accountName)) {
            return 'REZA';
        }
        
        if (preg_match('/---FEBRY(-|---|$|\s)/i', $accountName) || 
            preg_match('/FEBRY---/i', $accountName) ||
            strpos($accountName, 'IPO2024---Mohammad-Rocky-Pramana-HKU---2') !== false) {
            return 'FEBRY';
        }
        
        if (preg_match('/---LABIB(-|---|$|\s)/i', $accountName) || 
            preg_match('/LABIB---/i', $accountName) ||
            preg_match('/---GS---LABIB/i', $accountName) ||
            preg_match('/GS-LABIB/i', $accountName) ||
            preg_match('/RS-LABIB/i', $accountName) ||
            strpos($accountName, 'IPO2024---AI-YASYFI-YUTIMNA-HKU---3') !== false ||
            strpos($accountName, 'MKH---CLEORA-6') !== false) {
            return 'LABIB';
        }
        
        if (preg_match('/---ANGGA(-|---|$|\s)/i', $accountName) || 
            preg_match('/ANGGA---/i', $accountName) ||
            strpos($accountName, 'IPO2024---AGUS-HKU---') !== false ||
            strpos($accountName, 'CPAS---CLEORA---ANGGA') !== false ||
            strpos($accountName, 'IPO2024---AI-YASYFI-YUTIMNA-HKU---2') !== false) {
            // Check if it's "ANGGA,REZA" category
            if (preg_match('/---JB-BID---ANGGA/i', $accountName) ||
                preg_match('/---JB-ANGGA/i', $accountName) ||
                preg_match('/---GLOWSMOOTH---ANGGA/i', $accountName) ||
                strpos($accountName, 'MKH---CLEORA-7') !== false ||
                strpos($accountName, 'MKH---CLEORA-5') !== false ||
                strpos($accountName, 'MKH---CLEORA-8') !== false ||
                preg_match('/---ANGGA---3-MINUT/i', $accountName) ||
                preg_match('/---ANGGA---RS/i', $accountName) ||
                preg_match('/---RS---ANGGA/i', $accountName)) {
                return 'ANGGA,REZA';
            }
            return 'ANGGA';
        }
        
        if (strpos($accountName, 'TOFU') !== false ||
            strpos($accountName, 'MKH---CLEORA-4') !== false ||
            strpos($accountName, 'IPO2024---AI-YASYFI-YUTIMNA-HKU---1') !== false ||
            strpos($accountName, 'CLEORA-71---BOOST-OFFICIAL') !== false ||
            strpos($accountName, 'CLEORA-72---PROSES') !== false) {
            return 'TOFU';
        }
        
        if (strpos($accountName, 'CPAS-SHOPEE-CLEORA-1') !== false ||
            strpos($accountName, 'MKH---CPAS-SHOPEE-CLEORA-3') !== false ||
            strpos($accountName, 'IPO2024---Mohammad-Rocky-Pramana-HKU---1') !== false) {
            return 'ALL';
        }
        
        // Default case if no pattern matches
        return 'UNKNOWN';
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

            if ($request->has('pic') && $request->pic !== '') {
                $query->where('pic', $request->pic);
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
                    throw new \Exception("Invalid date format. Expected format: DD/MM/YYYY - DD/MM/YYYY");
                }
            }

            // Apply kategori_produk filter if provided
            $query = AdsMeta::select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            // Apply product category filter if provided
            if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
                $query->where('kategori_produk', $request->kategori_produk);
            }

            if ($request->has('pic') && $request->pic !== '') {
                $query->where('pic', $request->pic);
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
