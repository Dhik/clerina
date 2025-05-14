<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\AdsMeta;
use App\Domain\Sales\Models\AdsMeta2;
use App\Domain\Sales\Models\AdsMeta3;
use Illuminate\Support\Facades\Validator;
use App\Domain\Sales\Models\AdsTiktok;
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
use App\Domain\Sales\Services\GoogleSheetService;

class AdSpentSocialMediaController extends Controller
{
    protected $googleSheetService;
    public function __construct(
        protected AdSpentSocialMediaBLLInterface $adSpentSocialMediaBLL,
        protected SocialMediaBLLInterface $socialMediaBLL,
        GoogleSheetService $googleSheetService
    ) {
        $this->googleSheetService = $googleSheetService;
    }

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
            
        return view('admin.adSpentMarketPlace.ads_cpas_index_new', compact('kategoriProdukList', 'picList'));
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
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(CASE WHEN last_updated = date THEN account_name END) as last_updated_count'),
                DB::raw('COUNT(CASE WHEN new_created = date THEN account_name END) as new_created_count')
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
            ->addColumn('last_updated_count', function ($row) {
                return $row->last_updated_count ?? 0;
            })
            ->addColumn('new_created_count', function ($row) {
                return $row->new_created_count ?? 0;
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
                DB::raw('ANY_VALUE(id) as id'),
                DB::raw('MIN(date) as start_date'),
                DB::raw('MAX(date) as end_date'),
                DB::raw('SUM(amount_spent) as amount_spent'),
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views_shared_items'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart_shared_items'),
                DB::raw('SUM(purchases_shared_items) as purchases_shared_items'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as purchases_conversion_value_shared_items'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%TOFU%" THEN amount_spent ELSE 0 END) as tofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%MOFU%" THEN amount_spent ELSE 0 END) as mofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%BOFU%" THEN amount_spent ELSE 0 END) as bofu_spent'),
                DB::raw('COUNT(last_updated) as last_updated_count'),
                DB::raw('COUNT(new_created) as new_created_count'),
                'kategori_produk',
                'account_name'
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
        $query->groupBy('account_name', 'kategori_produk');
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $query->where('kategori_produk', $request->kategori_produk);
        }
        if ($request->has('pic') && $request->pic !== '') {
            $query->where('pic', $request->pic);
        }
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('account_name', function ($row) {
                return $row->account_name ?: '-';
            })
            ->editColumn('date', function ($row) {
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
            ->addColumn('mofu_spent', function ($row) {
                return $row->mofu_spent ? 'Rp ' . number_format($row->mofu_spent, 0, ',', '.') : '-';
            })
            ->addColumn('mofu_percentage', function ($row) {
                if ($row->amount_spent > 0 && $row->mofu_spent > 0) {
                    $percentage = ($row->mofu_spent / $row->amount_spent) * 100;
                    return number_format($percentage, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('bofu_spent', function ($row) {
                return $row->bofu_spent ? 'Rp ' . number_format($row->bofu_spent, 0, ',', '.') : '-';
            })
            ->addColumn('bofu_percentage', function ($row) {
                if ($row->amount_spent > 0 && $row->bofu_spent > 0) {
                    $percentage = ($row->bofu_spent / $row->amount_spent) * 100;
                    return number_format($percentage, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('last_updated_count', function ($row) {
                return $row->last_updated_count ?: '0';
            })
            ->addColumn('new_created_count', function ($row) {
                return $row->new_created_count ?: '0';
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
    DB::beginTransaction();
    try {
        $validator = Validator::make($request->all(), [
            'account_name' => 'required|string',
            'date' => 'required|date_format:Y-m-d',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Debug output to check the incoming data
        \Log::info('Delete Request:', [
            'account_name' => $request->account_name,
            'date' => $request->date,
            // 'tenant_id' => auth()->user()->current_tenant_id ?? auth()->user()->tenant_id
        ]);

        // Ensure we have a tenant ID
        // $tenantId = auth()->user()->current_tenant_id ?? auth()->user()->tenant_id;
        // if (!$tenantId) {
        //     return response()->json([
        //         'status' => 'error',
        //         'message' => 'No tenant ID found for the current user.'
        //     ], 400);
        // }

        $date = Carbon::parse($request->date)->format('Y-m-d');
        
        // Check if records exist before attempting to delete
        $records = AdsMeta::where('account_name', $request->account_name)
            ->where('date', $date)
            // ->where('tenant_id', $tenantId)
            ->get();
            
        \Log::info('Found records:', ['count' => $records->count()]);
        
        if ($records->count() > 0) {
            $deleted = AdsMeta::where('account_name', $request->account_name)
                ->where('date', $date)
                // ->where('tenant_id', $tenantId)
                ->delete();
                
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Account data deleted successfully for the specified date.',
                'deleted_count' => $deleted
            ]);
        } else {
            // Try a more lenient query to see what might be available
            $possibleRecords = AdsMeta::where('date', $date)
                // ->where('tenant_id', $tenantId)
                ->get();
                
            $availableAccounts = $possibleRecords->pluck('account_name')->unique()->toArray();
            
            \Log::info('Available accounts for date:', [
                'date' => $date,
                'accounts' => $availableAccounts
            ]);
            
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'No records found to delete. Please check the account name and date.',
                'debug_info' => [
                    'requested_account' => $request->account_name,
                    'requested_date' => $date,
                    'available_accounts' => $availableAccounts,
                ]
            ], 404);
        }
        
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Delete error: ' . $e->getMessage(), [
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'status' => 'error',
            'message' => 'Error deleting data: ' . $e->getMessage(),
            'debug_info' => [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'exception_class' => get_class($e)
            ]
        ], 500);
    }
}


    public function get_shopee2_ads_cpas(Request $request)
    {
        $query = AdsMeta2::query()
            ->select([
                DB::raw('date'),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(CASE WHEN last_updated = date THEN account_name END) as last_updated_count'),
                DB::raw('COUNT(CASE WHEN new_created = date THEN account_name END) as new_created_count')
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
            ->addColumn('last_updated_count', function ($row) {
                return $row->last_updated_count ?? 0;
            })
            ->addColumn('new_created_count', function ($row) {
                return $row->new_created_count ?? 0;
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

    public function get_shopee3_ads_cpas(Request $request)
    {
        $query = AdsMeta3::query()
            ->select([
                DB::raw('date'),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(CASE WHEN last_updated = date THEN account_name END) as last_updated_count'),
                DB::raw('COUNT(CASE WHEN new_created = date THEN account_name END) as new_created_count')
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
            ->addColumn('last_updated_count', function ($row) {
                return $row->last_updated_count ?? 0;
            })
            ->addColumn('new_created_count', function ($row) {
                return $row->new_created_count ?? 0;
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

    public function get_tiktok_ads_cpas(Request $request)
    {
        $query = AdsTiktok::query()
            ->select([
                DB::raw('date'),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(CASE WHEN last_updated = date THEN account_name END) as last_updated_count'),
                DB::raw('COUNT(CASE WHEN new_created = date THEN account_name END) as new_created_count')
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
            ->addColumn('last_updated_count', function ($row) {
                return $row->last_updated_count ?? 0;
            })
            ->addColumn('new_created_count', function ($row) {
                return $row->new_created_count ?? 0;
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

    public function get_platform_comparison_data(Request $request)
    {
        try {
            // Get data from all platforms
            $shopeeMallQuery = AdsMeta::query()->select(
                DB::raw("'Shopee Mall' as platform"),
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue')
            );
            
            $shopee2Query = AdsMeta2::query()->select(
                DB::raw("'Shopee 2' as platform"),
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue')
            );
            
            $shopee3Query = AdsMeta3::query()->select(
                DB::raw("'Shopee 3' as platform"),
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue')
            );
            
            $tiktokQuery = AdsTiktok::query()->select(
                DB::raw("'TikTok' as platform"),
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue')
            );
            
            // Apply filters to all queries
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $shopeeMallQuery->whereBetween('date', [$startDate, $endDate]);
                $shopee2Query->whereBetween('date', [$startDate, $endDate]);
                $shopee3Query->whereBetween('date', [$startDate, $endDate]);
                $tiktokQuery->whereBetween('date', [$startDate, $endDate]);
            } else {
                // Default to current month
                $shopeeMallQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $shopee2Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $shopee3Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $tiktokQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
            }
            
            // Apply category filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $shopeeMallQuery->where('kategori_produk', $request->kategori_produk);
                $shopee2Query->where('kategori_produk', $request->kategori_produk);
                $shopee3Query->where('kategori_produk', $request->kategori_produk);
                $tiktokQuery->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply PIC filter
            if ($request->has('pic') && $request->pic) {
                $shopeeMallQuery->where('pic', $request->pic);
                $shopee2Query->where('pic', $request->pic);
                $shopee3Query->where('pic', $request->pic);
                $tiktokQuery->where('pic', $request->pic);
            }
            
            // Get data from all platforms
            $shopeeMallData = $shopeeMallQuery->first();
            $shopee2Data = $shopee2Query->first();
            $shopee3Data = $shopee3Query->first();
            $tiktokData = $tiktokQuery->first();
            
            // Combine data for chart
            $platforms = ['Shopee Mall', 'Shopee 2', 'Shopee 3', 'TikTok'];
            $spent = [
                $shopeeMallData ? (float)$shopeeMallData->spent : 0,
                $shopee2Data ? (float)$shopee2Data->spent : 0,
                $shopee3Data ? (float)$shopee3Data->spent : 0,
                $tiktokData ? (float)$tiktokData->spent : 0
            ];
            $revenue = [
                $shopeeMallData ? (float)$shopeeMallData->revenue : 0,
                $shopee2Data ? (float)$shopee2Data->revenue : 0,
                $shopee3Data ? (float)$shopee3Data->revenue : 0,
                $tiktokData ? (float)$tiktokData->revenue : 0
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'labels' => $platforms,
                    'spent' => $spent,
                    'revenue' => $revenue
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function get_overall_performance(Request $request)
    {
        // Create a union of all data sources with updated platform names
        $shopeeMallQuery = AdsMeta::query()
            ->select([
                'date',
                DB::raw("'Shopee Mall' as platform"), 
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value')
            ])
            ->groupBy('date');
            
        $shopee2Query = AdsMeta2::query()
            ->select([
                'date',
                DB::raw("'Shopee 2' as platform"),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value')
            ])
            ->groupBy('date');
            
        $shopee3Query = AdsMeta3::query()
            ->select([
                'date',
                DB::raw("'Shopee 3' as platform"),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value')
            ])
            ->groupBy('date');
            
        $tiktokQuery = AdsTiktok::query()
            ->select([
                'date',
                DB::raw("'TikTok' as platform"),
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value')
            ])
            ->groupBy('date');
    
        // Apply filters to all queries
        if (auth()->user()->tenant_id) {
            $shopeeMallQuery->where('tenant_id', auth()->user()->tenant_id);
            $shopee2Query->where('tenant_id', auth()->user()->tenant_id);
            $shopee3Query->where('tenant_id', auth()->user()->tenant_id);
            $tiktokQuery->where('tenant_id', auth()->user()->tenant_id);
        }
        
        if ($request->has('date_start') && $request->has('date_end')) {
            $shopeeMallQuery->whereBetween('date', [$request->date_start, $request->date_end]);
            $shopee2Query->whereBetween('date', [$request->date_start, $request->date_end]);
            $shopee3Query->whereBetween('date', [$request->date_start, $request->date_end]);
            $tiktokQuery->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $shopeeMallQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
            $shopee2Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
            $shopee3Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
            $tiktokQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
        }
        
        if ($request->has('kategori_produk') && $request->kategori_produk) {
            $shopeeMallQuery->where('kategori_produk', $request->kategori_produk);
            $shopee2Query->where('kategori_produk', $request->kategori_produk);
            $shopee3Query->where('kategori_produk', $request->kategori_produk);
            $tiktokQuery->where('kategori_produk', $request->kategori_produk);
        }

        if ($request->has('pic') && $request->pic) {
            $shopeeMallQuery->where('pic', $request->pic);
            $shopee2Query->where('pic', $request->pic);
            $shopee3Query->where('pic', $request->pic);
            $tiktokQuery->where('pic', $request->pic);
        }
        
        // Combine the queries using union
        $query = $shopeeMallQuery->unionAll($shopee2Query)->unionAll($shopee3Query)->unionAll($tiktokQuery);
    
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d M Y');
            })
            // Add calculated columns
            ->addColumn('cost_per_purchase', function ($row) {
                if ($row->total_purchases > 0 && $row->total_amount_spent > 0) {
                    return $row->total_amount_spent / $row->total_purchases;
                }
                return 0;
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
            ->addColumn('compare', function ($row) {
                return '<button type="button" class="btn btn-info btn-sm compare-platform" data-date="'.$row->date.'" data-platform="'.$row->platform.'">
                    <i class="fas fa-chart-line"></i> Compare
                </button>';
            })
            ->rawColumns(['performance', 'compare'])
            ->make(true);
    }

    public function get_overall_metrics_data(Request $request)
    {
        try {
            // Get aggregated data from all platforms
            $shopeeMallQuery = AdsMeta::query()->select(
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            );
            
            $shopee2Query = AdsMeta2::query()->select(
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            );
            
            $shopee3Query = AdsMeta3::query()->select(
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            );
            
            $tiktokQuery = AdsTiktok::query()->select(
                DB::raw('SUM(amount_spent) as spent'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as revenue'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            );
            
            // Apply filters
            if ($request->has('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                $startDate = Carbon::createFromFormat('d/m/Y', trim($dates[0]))->format('Y-m-d');
                $endDate = Carbon::createFromFormat('d/m/Y', trim($dates[1]))->format('Y-m-d');
                
                $shopeeMallQuery->whereBetween('date', [$startDate, $endDate]);
                $shopee2Query->whereBetween('date', [$startDate, $endDate]);
                $shopee3Query->whereBetween('date', [$startDate, $endDate]);
                $tiktokQuery->whereBetween('date', [$startDate, $endDate]);
            } else {
                // Default to current month
                $shopeeMallQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $shopee2Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $shopee3Query->whereMonth('date', now()->month)->whereYear('date', now()->year);
                $tiktokQuery->whereMonth('date', now()->month)->whereYear('date', now()->year);
            }
            
            // Apply other filters
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $shopeeMallQuery->where('kategori_produk', $request->kategori_produk);
                $shopee2Query->where('kategori_produk', $request->kategori_produk);
                $shopee3Query->where('kategori_produk', $request->kategori_produk);
                $tiktokQuery->where('kategori_produk', $request->kategori_produk);
            }
            
            if ($request->has('pic') && $request->pic) {
                $shopeeMallQuery->where('pic', $request->pic);
                $shopee2Query->where('pic', $request->pic);
                $shopee3Query->where('pic', $request->pic);
                $tiktokQuery->where('pic', $request->pic);
            }
            
            // Get data
            $shopeeMallData = $shopeeMallQuery->first();
            $shopee2Data = $shopee2Query->first();
            $shopee3Data = $shopee3Query->first();
            $tiktokData = $tiktokQuery->first();
            
            // Calculate total metrics
            $totalSpent = 
                ($shopeeMallData ? (float)$shopeeMallData->spent : 0) + 
                ($shopee2Data ? (float)$shopee2Data->spent : 0) + 
                ($shopee3Data ? (float)$shopee3Data->spent : 0) + 
                ($tiktokData ? (float)$tiktokData->spent : 0);
                
            $totalRevenue = 
                ($shopeeMallData ? (float)$shopeeMallData->revenue : 0) + 
                ($shopee2Data ? (float)$shopee2Data->revenue : 0) + 
                ($shopee3Data ? (float)$shopee3Data->revenue : 0) + 
                ($tiktokData ? (float)$tiktokData->revenue : 0);
                
            $totalPurchases = 
                ($shopeeMallData ? (float)$shopeeMallData->purchases : 0) + 
                ($shopee2Data ? (float)$shopee2Data->purchases : 0) + 
                ($shopee3Data ? (float)$shopee3Data->purchases : 0) + 
                ($tiktokData ? (float)$tiktokData->purchases : 0);
            
            // Calculate platform-specific metrics
            $shopeeMallRoas = $shopeeMallData && $shopeeMallData->spent > 0 ? $shopeeMallData->revenue / $shopeeMallData->spent : 0;
            $shopee2Roas = $shopee2Data && $shopee2Data->spent > 0 ? $shopee2Data->revenue / $shopee2Data->spent : 0;
            $shopee3Roas = $shopee3Data && $shopee3Data->spent > 0 ? $shopee3Data->revenue / $shopee3Data->spent : 0;
            $tiktokRoas = $tiktokData && $tiktokData->spent > 0 ? $tiktokData->revenue / $tiktokData->spent : 0;
            
            $shopeeMallCPP = $shopeeMallData && $shopeeMallData->purchases > 0 ? $shopeeMallData->spent / $shopeeMallData->purchases : 0;
            $shopee2CPP = $shopee2Data && $shopee2Data->purchases > 0 ? $shopee2Data->spent / $shopee2Data->purchases : 0;
            $shopee3CPP = $shopee3Data && $shopee3Data->purchases > 0 ? $shopee3Data->spent / $shopee3Data->purchases : 0;
            $tiktokCPP = $tiktokData && $tiktokData->purchases > 0 ? $tiktokData->spent / $tiktokData->purchases : 0;
            
            // Calculate overall ROAS
            $overallRoas = $totalSpent > 0 ? $totalRevenue / $totalSpent : 0;
            
            // Calculate average CPP
            $avgCPP = $totalPurchases > 0 ? $totalSpent / $totalPurchases : 0;
            
            // Prepare data for radial chart
            $series = [
                $overallRoas > 0 ? min($overallRoas * 40, 100) : 0, // Convert ROAS to percentage (capped at 100%)
                $totalPurchases > 0 ? min($totalPurchases / 100, 100) : 0, // Convert purchases to percentage
                $totalRevenue > 0 ? min($totalRevenue / 1000000 * 50, 100) : 0, // Convert revenue to percentage
                $totalSpent > 0 && $totalRevenue > 0 ? min(($totalRevenue / $totalSpent) * 25, 100) : 0 // Efficiency score
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => [
                    'labels' => ['ROAS', 'Conversions', 'Revenue', 'Efficiency'],
                    'series' => $series,
                    'metrics' => [
                        'total_spent' => $totalSpent,
                        'total_revenue' => $totalRevenue,
                        'total_purchases' => $totalPurchases,
                        'overall_roas' => $overallRoas,
                        'avg_cpp' => $avgCPP,
                        'spent_breakdown' => [
                            'Shopee Mall' => $shopeeMallData ? (float)$shopeeMallData->spent : 0,
                            'Shopee 2' => $shopee2Data ? (float)$shopee2Data->spent : 0,
                            'Shopee 3' => $shopee3Data ? (float)$shopee3Data->spent : 0,
                            'TikTok' => $tiktokData ? (float)$tiktokData->spent : 0
                        ],
                        'revenue_breakdown' => [
                            'Shopee Mall' => $shopeeMallData ? (float)$shopeeMallData->revenue : 0,
                            'Shopee 2' => $shopee2Data ? (float)$shopee2Data->revenue : 0,
                            'Shopee 3' => $shopee3Data ? (float)$shopee3Data->revenue : 0,
                            'TikTok' => $tiktokData ? (float)$tiktokData->revenue : 0
                        ],
                        'roas_breakdown' => [
                            'Shopee Mall ROAS' => $shopeeMallRoas,
                            'Shopee 2 ROAS' => $shopee2Roas,
                            'Shopee 3 ROAS' => $shopee3Roas,
                            'TikTok ROAS' => $tiktokRoas
                        ],
                        'purchases_breakdown' => [
                            'Shopee Mall' => $shopeeMallData ? (float)$shopeeMallData->purchases : 0,
                            'Shopee 2' => $shopee2Data ? (float)$shopee2Data->purchases : 0,
                            'Shopee 3' => $shopee3Data ? (float)$shopee3Data->purchases : 0,
                            'TikTok' => $tiktokData ? (float)$tiktokData->purchases : 0
                        ],
                        'cpp_breakdown' => [
                            'Shopee Mall CPP' => $shopeeMallCPP,
                            'Shopee 2 CPP' => $shopee2CPP,
                            'Shopee 3 CPP' => $shopee3CPP,
                            'TikTok CPP' => $tiktokCPP
                        ]
                    ]
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function get_shopee2_line_data(Request $request)
    {
        try {
            $query = AdsMeta2::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Group by date and get sum of impressions
            $impressionData = $query->select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'impressions' => (int)$item->impressions
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'impressions' => $impressionData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_shopee3_line_data(Request $request)
    {
        try {
            $query = AdsMeta3::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Group by date and get sum of impressions
            $impressionData = $query->select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'impressions' => (int)$item->impressions
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'impressions' => $impressionData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_tiktok_line_data(Request $request)
    {
        try {
            $query = AdsTiktok::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Group by date and get sum of impressions
            $impressionData = $query->select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'impressions' => (int)$item->impressions
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'impressions' => $impressionData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }

    public function get_shopee2_funnel_data(Request $request)
    {
        try {
            $query = AdsMeta2::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            )->first();
            
            // Prepare data for funnel chart
            $funnelData = [
                [
                    'name' => 'Impressions',
                    'value' => (int)$aggregates->impressions
                ],
                [
                    'name' => 'Link Clicks',
                    'value' => (int)$aggregates->link_clicks
                ],
                [
                    'name' => 'Content Views',
                    'value' => (int)$aggregates->content_views
                ],
                [
                    'name' => 'Add to Cart',
                    'value' => (int)$aggregates->adds_to_cart
                ],
                [
                    'name' => 'Purchases',
                    'value' => (int)$aggregates->purchases
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_shopee3_funnel_data(Request $request)
    {
        try {
            $query = AdsMeta3::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            )->first();
            
            // Prepare data for funnel chart
            $funnelData = [
                [
                    'name' => 'Impressions',
                    'value' => (int)$aggregates->impressions
                ],
                [
                    'name' => 'Link Clicks',
                    'value' => (int)$aggregates->link_clicks
                ],
                [
                    'name' => 'Content Views',
                    'value' => (int)$aggregates->content_views
                ],
                [
                    'name' => 'Add to Cart',
                    'value' => (int)$aggregates->adds_to_cart
                ],
                [
                    'name' => 'Purchases',
                    'value' => (int)$aggregates->purchases
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_tiktok_funnel_data(Request $request)
    {
        try {
            $query = AdsTiktok::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            )->first();
            
            // Prepare data for funnel chart
            $funnelData = [
                [
                    'name' => 'Impressions',
                    'value' => (int)$aggregates->impressions
                ],
                [
                    'name' => 'Content Views',
                    'value' => (int)$aggregates->content_views
                ],
                [
                    'name' => 'Link Clicks',
                    'value' => (int)$aggregates->link_clicks
                ],
                [
                    'name' => 'Add to Cart',
                    'value' => (int)$aggregates->adds_to_cart
                ],
                [
                    'name' => 'Purchases',
                    'value' => (int)$aggregates->purchases
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_google_line_data(Request $request)
    {
        try {
            $query = AdsGoogle::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Group by date and get sum of impressions
            $impressionData = $query->select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'impressions' => (int)$item->impressions
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'impressions' => $impressionData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_google_funnel_data(Request $request)
    {
        try {
            $query = AdsGoogle::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            )->first();
            
            // Prepare data for funnel chart
            $funnelData = [
                [
                    'name' => 'Impressions',
                    'value' => (int)$aggregates->impressions
                ],
                [
                    'name' => 'Link Clicks',
                    'value' => (int)$aggregates->link_clicks
                ],
                [
                    'name' => 'Content Views',
                    'value' => (int)$aggregates->content_views
                ],
                [
                    'name' => 'Add to Cart',
                    'value' => (int)$aggregates->adds_to_cart
                ],
                [
                    'name' => 'Purchases',
                    'value' => (int)$aggregates->purchases
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function export_overall_report(Request $request)
    {
        try {
            // Create an export file with data from all platforms
            $fileName = 'overall_ads_performance_' . date('Y-m-d') . '.xlsx';
            
            // You'll need to implement the actual export logic based on your requirements
            // For example, you might want to create a new Excel export class
            
            return Excel::download(new OverallPerformanceExport($request), $fileName);
            
        } catch (\Exception $e) {
            return back()->with('error', 'Error exporting report: ' . $e->getMessage());
        }
    }
    public function get_line_data(Request $request)
    {
        try {
            $query = AdsMeta::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Group by date and get sum of impressions
            $impressionData = $query->select(
                'date',
                DB::raw('SUM(impressions) as impressions')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => Carbon::parse($item->date)->format('d M Y'),
                    'impressions' => (int)$item->impressions
                ];
            });
            
            return response()->json([
                'status' => 'success',
                'impressions' => $impressionData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
    public function get_funnel_data(Request $request)
    {
        try {
            $query = AdsMeta::query();
            
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
            
            // Apply kategori_produk filter
            if ($request->has('kategori_produk') && $request->kategori_produk) {
                $query->where('kategori_produk', $request->kategori_produk);
            }
            
            // Apply pic filter
            if ($request->has('pic') && $request->pic) {
                $query->where('pic', $request->pic);
            }
            
            // Get aggregate data
            $aggregates = $query->select(
                DB::raw('SUM(impressions) as impressions'),
                DB::raw('SUM(link_clicks) as link_clicks'),
                DB::raw('SUM(content_views_shared_items) as content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as purchases')
            )->first();
            
            // Prepare data for funnel chart
            $funnelData = [
                [
                    'name' => 'Impressions',
                    'value' => (int)$aggregates->impressions
                ],
                [
                    'name' => 'Link Clicks',
                    'value' => (int)$aggregates->link_clicks
                ],
                [
                    'name' => 'Content Views',
                    'value' => (int)$aggregates->content_views
                ],
                [
                    'name' => 'Add to Cart',
                    'value' => (int)$aggregates->adds_to_cart
                ],
                [
                    'name' => 'Purchases',
                    'value' => (int)$aggregates->purchases
                ]
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 422);
        }
    }
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
                        'tenant_id' => Auth::user()->current_tenant_id,
                        'new_created' => $newCreated,
                        'last_updated' => $lastUpdated
                    ],
                    [
                        'impressions' => (int)$row[4],
                        'content_views_shared_items' => (float)($row[5] ?? 0),
                        'adds_to_cart_shared_items' => (float)($row[6] ?? 0),
                        'purchases_shared_items' => (float)($row[7] ?? 0),
                        'purchases_conversion_value_shared_items' => (float)($row[8] ?? 0),
                        'link_clicks' => (float)($row[9] ?? 0),
                        'account_name' => $accountName,
                        'pic' => $pic
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
    public function exportAdsMetaStats()
    {
        $newSpreadsheetId = '17Sls8V-UH5gWobRCqxdeI8IySx7a7Nf7HGs3cDUI2F8';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        
        $startDate = '2025-03-01';
        $endDate = '2025-04-30';
        
        $records = AdsMeta::selectRaw(
            'kategori_produk, 
            date,
            SUM(amount_spent) AS total_amount_spent,
            SUM(impressions) AS total_impressions,
            SUM(link_clicks) AS total_link_clicks,
            SUM(content_views_shared_items) AS total_content_views,
            SUM(adds_to_cart_shared_items) AS total_adds_to_cart,
            SUM(purchases_shared_items) AS total_purchases,
            SUM(purchases_conversion_value_shared_items) AS total_conversion_value'
        )
        ->whereBetween('date', [$startDate, $endDate])
        ->groupBy('kategori_produk', 'date')
        ->orderBy('kategori_produk')
        ->orderBy('date')
        ->get();
        
        $data = [];
        $data[] = [
            'Kategori Produk',
            'Date', 
            'Total Amount Spent',
            'Total Impressions', 
            'Total Link Clicks',
            'Total Content Views',
            'Total Adds to Cart',
            'Total Purchases',
            'Total Conversion Value'
        ];
        
        foreach ($records as $row) {
            $data[] = [
                $row->kategori_produk,
                Carbon::parse($row->date)->format('Y-m-d'),
                (float)$row->total_amount_spent,
                (int)$row->total_impressions,
                (float)$row->total_link_clicks,
                (float)$row->total_content_views,
                (float)$row->total_adds_to_cart,
                (float)$row->total_purchases,
                (float)$row->total_conversion_value
            ];
        }
        
        // Update sheet name
        $sheetName = 'Ads Meta Stats';
        
        $this->googleSheetService->clearRange("$sheetName!A1:I1000");
        $this->googleSheetService->exportData("$sheetName!A1", $data, 'USER_ENTERED');
        
        // Calculate total conversion value across all records
        $totalConversionValueSum = $records->sum('total_conversion_value');
        
        return response()->json([
            'success' => true, 
            'message' => 'Ads Meta Stats exported successfully to Google Sheets',
            'date_range' => "$startDate to $endDate",
            'count' => count($data) - 1,
            'total_conversion_value_sum' => $totalConversionValueSum
        ]);
    }

    public function import_shopee2_ads(Request $request)
    {
        try {
            $request->validate([
                'shopee2AdsFile' => 'required|file|mimes:csv,txt,zip|max:5120', // Increased size limit for ZIP
                'kategori_produk' => 'required|string'
            ]);

            $file = $request->file('shopee2AdsFile');
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
                            $importCount += $this->processShopee2CsvFile($csvFile, $kategoriProduk, $dateAmountMap, $csvFilename);
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
                    $importCount = $this->processShopee2CsvFile($file->getPathname(), $kategoriProduk, $dateAmountMap, $originalFilename);
                }

                // Update AdSpentSocialMedia with aggregated totals (if needed)
                foreach ($dateAmountMap as $date => $totalAmount) {
                    AdSpentSocialMedia::updateOrCreate(
                        [
                            'date' => $date,
                            'social_media_id' => 2, // Assuming 2 is for Shopee 2
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
                    'message' => 'Shopee 2 ads data imported successfully. ' . $importCount . ' records imported.'
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
    private function processShopee2CsvFile($filePath, $kategoriProduk, &$dateAmountMap, $originalFilename = null)
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
                
                AdsMeta2::updateOrCreate(
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
    public function import_shopee3_ads(Request $request)
    {
        try {
            $request->validate([
                'shopee3AdsFile' => 'required|file|mimes:csv,txt,zip|max:5120',
                'kategori_produk' => 'required|string'
            ]);

            $file = $request->file('shopee3AdsFile');
            $kategoriProduk = $request->input('kategori_produk');
            $dateAmountMap = [];
            $importCount = 0;

            DB::beginTransaction();
            try {
                // Check if the file is a ZIP file
                if ($file->getClientOriginalExtension() == 'zip') {
                    // Similar implementation to above, extracting zip and processing files
                    $tempDir = storage_path('app/temp/') . uniqid('zip_extract_');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    
                    // Extract and process ZIP contents
                    $zip = new \ZipArchive;
                    if ($zip->open($file->getPathname()) === TRUE) {
                        $zip->extractTo($tempDir);
                        $zip->close();
                        
                        $csvFiles = glob($tempDir . '/*.csv');
                        foreach ($csvFiles as $csvFile) {
                            $csvFilename = basename($csvFile);
                            $importCount += $this->processShopee3CsvFile($csvFile, $kategoriProduk, $dateAmountMap, $csvFilename);
                        }
                        
                        array_map('unlink', glob($tempDir . '/*'));
                        rmdir($tempDir);
                    } else {
                        throw new \Exception("Could not open ZIP file");
                    }
                } else {
                    // Process a single CSV file
                    $originalFilename = $file->getClientOriginalName();
                    $importCount = $this->processShopee3CsvFile($file->getPathname(), $kategoriProduk, $dateAmountMap, $originalFilename);
                }

                // Update AdSpentSocialMedia if needed
                foreach ($dateAmountMap as $date => $totalAmount) {
                    AdSpentSocialMedia::updateOrCreate(
                        [
                            'date' => $date,
                            'social_media_id' => 3, // Assuming 3 is for Shopee 3
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
                    'message' => 'Shopee 3 ads data imported successfully. ' . $importCount . ' records imported.'
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
    private function processShopee3CsvFile($filePath, $kategoriProduk, &$dateAmountMap, $originalFilename = null)
    {
        // Similar implementation to processShopee2CsvFile but using AdsMeta3 model
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
        
        // Process each row
        foreach ($csvData as $row) {
            if (empty($row[0])) {
                continue;
            }
            
            try {
                // Extract and process data
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
                
                // Update or create record in AdsMeta3
                AdsMeta3::updateOrCreate(
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
    public function import_tiktok_ads(Request $request)
    {
        try {
            $request->validate([
                'tiktokAdsFile' => 'required|file|mimes:xlsx,zip|max:5120',
                'kategori_produk' => 'required|string',
                'pic' => 'required|string'
            ]);

            $file = $request->file('tiktokAdsFile');
            $kategoriProduk = $request->input('kategori_produk');
            $pic = $request->input('pic');
            $dateAmountMap = [];
            $importCount = 0;
            
            \Log::info("Starting TikTok ads import process");
            \Log::info("File name: " . $file->getClientOriginalName());
            \Log::info("Kategori produk: " . $kategoriProduk);
            \Log::info("PIC: " . $pic);

            // Capturing tenant ID early to ensure it's available
            $tenantId = Auth::user()->current_tenant_id;
            \Log::info("Using tenant ID: " . $tenantId);

            // Test database connection and model
            try {
                $totalRecords = DB::table('ads_tiktok')->count();
                \Log::info("Current total records in ads_tiktok: " . $totalRecords);
            } catch (\Exception $e) {
                \Log::error("Error checking database: " . $e->getMessage());
            }

            DB::beginTransaction();
            try {
                // Check if the file is a ZIP file
                if ($file->getClientOriginalExtension() == 'zip') {
                    \Log::info("Processing ZIP file");
                    // Process ZIP file
                    $tempDir = storage_path('app/temp/') . uniqid('zip_extract_');
                    if (!file_exists($tempDir)) {
                        mkdir($tempDir, 0755, true);
                    }
                    
                    $zip = new \ZipArchive;
                    if ($zip->open($file->getPathname()) === TRUE) {
                        $zip->extractTo($tempDir);
                        $zip->close();
                        
                        $xlsxFiles = glob($tempDir . '/*.xlsx');
                        \Log::info("Found " . count($xlsxFiles) . " XLSX files in ZIP");
                        
                        foreach ($xlsxFiles as $xlsxFile) {
                            $xlsxFilename = basename($xlsxFile);
                            \Log::info("Processing XLSX file from ZIP: " . $xlsxFilename);
                            $importCount += $this->processTiktokXlsxFile($xlsxFile, $kategoriProduk, $pic, $dateAmountMap, $xlsxFilename, $tenantId);
                        }
                        
                        array_map('unlink', glob($tempDir . '/*'));
                        rmdir($tempDir);
                    } else {
                        throw new \Exception("Could not open ZIP file");
                    }
                } else {
                    // Process a single XLSX file
                    $originalFilename = $file->getClientOriginalName();
                    \Log::info("Processing single XLSX file: " . $originalFilename);
                    $importCount = $this->processTiktokXlsxFile($file->getPathname(), $kategoriProduk, $pic, $dateAmountMap, $originalFilename, $tenantId);
                }

                // Update AdSpentSocialMedia with aggregated totals
                foreach ($dateAmountMap as $date => $totalAmount) {
                    \Log::info("Updating AdSpentSocialMedia for date: " . $date . " with amount: " . $totalAmount);
                    
                    try {
                        AdSpentSocialMedia::updateOrCreate(
                            [
                                'date' => $date,
                                'social_media_id' => 4, // Assuming 4 is for TikTok
                                'tenant_id' => $tenantId
                            ],
                            [
                                'amount' => $totalAmount
                            ]
                        );
                        \Log::info("AdSpentSocialMedia updated successfully for date: " . $date);
                    } catch (\Exception $e) {
                        \Log::error("Error updating AdSpentSocialMedia: " . $e->getMessage());
                    }
                }
                
                DB::commit();
                \Log::info("Transaction committed, total imported: " . $importCount);
                
                return response()->json([
                    'status' => 'success',
                    'message' => 'TikTok ads data imported successfully. ' . $importCount . ' records imported.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Import failed with error: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error("Error in import_tiktok_ads: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }

    private function processTiktokXlsxFile($filePath, $kategoriProduk, $pic, &$dateAmountMap, $originalFilename = null, $tenantId = null)
    {
        // Get account_name from filename
        $filename = $originalFilename ?? basename($filePath);
        $filename = pathinfo($filename, PATHINFO_FILENAME);
        
        \Log::info("Processing file: " . $filename);
        
        // Extract account_name and date from filename pattern
        // More flexible pattern matching
        $reportDate = null;
        $accountName = null;
        
        if (preg_match('/Report(\d{4})_(\d{2})_(\d{2})/', $filename, $matches)) {
            $accountName = preg_replace('/_Campaign_Report.*$/', '', $filename);
            $reportDate = $matches[1] . '-' . $matches[2] . '-' . $matches[3];
            \Log::info("Extracted date from filename: " . $reportDate);
            \Log::info("Extracted account name: " . $accountName);
        } else {
            $accountName = $filename;
            $reportDate = Carbon::now()->format('Y-m-d');
            \Log::info("Using default date: " . $reportDate . " and account name: " . $accountName);
        }
        
        // Use Laravel Excel to read the XLSX file
        try {
            $data = \Maatwebsite\Excel\Facades\Excel::toArray(new class {}, $filePath)[0];
            \Log::info("Excel file loaded successfully with " . count($data) . " rows");
            
            // For debugging, log the first few rows
            for ($i = 0; $i < min(3, count($data)); $i++) {
                \Log::info("Row $i: " . json_encode(array_slice($data[$i], 0, 5)) . "...");
            }
        } catch (\Exception $e) {
            \Log::error("Error loading Excel file: " . $e->getMessage());
            return 0;
        }
        
        // Find the header row with more flexible matching
        $headerRow = null;
        $columnMap = [];
        
        foreach ($data as $index => $row) {
            // Look for common headers that should exist in the file
            $campaignNameFound = false;
            $costFound = false;
            
            foreach ($row as $colIndex => $value) {
                if (is_string($value)) {
                    if (stripos($value, 'Campaign name') !== false || stripos($value, 'Campaign') !== false) {
                        $campaignNameFound = true;
                    }
                    if (stripos($value, 'Cost') !== false) {
                        $costFound = true;
                    }
                }
            }
            
            if ($campaignNameFound && $costFound) {
                $headerRow = $index;
                
                // Create column mapping
                foreach ($row as $colIndex => $colName) {
                    if (!empty($colName)) {
                        $columnMap[$colName] = $colIndex;
                    }
                }
                
                \Log::info("Found header row at index " . $index . " with columns: " . implode(", ", array_keys($columnMap)));
                break;
            }
        }
        
        // If header row not found, try a simpler approach
        if ($headerRow === null) {
            \Log::warning("Header row not found using standard detection. Using first row as header.");
            $headerRow = 0;
            
            foreach ($data[0] as $colIndex => $colName) {
                if (!empty($colName)) {
                    $columnMap[$colName] = $colIndex;
                }
            }
        }
        
        // If still no header found, log error and return
        if (empty($columnMap)) {
            \Log::error("Could not find any headers in the Excel file");
            return 0;
        }
        
        // Skip header row
        $dataRows = array_slice($data, $headerRow + 1);
        $count = 0;
        
        // Check for key columns
        $requiredColumns = ['Campaign name', 'Cost', 'Impressions'];
        $missingColumns = [];
        
        foreach ($requiredColumns as $column) {
            if (!isset($columnMap[$column])) {
                $missingColumns[] = $column;
            }
        }
        
        if (!empty($missingColumns)) {
            \Log::warning("Missing required columns: " . implode(", ", $missingColumns));
            // Try to find similar column names
            foreach ($missingColumns as $missing) {
                foreach (array_keys($columnMap) as $existingColumn) {
                    if (similar_text($missing, $existingColumn) / strlen($missing) > 0.7) {
                        \Log::info("Using '$existingColumn' as a substitute for '$missing'");
                        $columnMap[$missing] = $columnMap[$existingColumn];
                        break;
                    }
                }
            }
        }
        
        // Create a function to safely get column values
        $getColumnValue = function($row, $columnName, $default = null) use ($columnMap) {
            return isset($columnMap[$columnName]) && isset($row[$columnMap[$columnName]]) 
                ? $row[$columnMap[$columnName]] 
                : $default;
        };
        
        // Insert a test record to verify database connection
        try {
            $testRecord = new AdsTiktok();
            $testRecord->date = $reportDate;
            $testRecord->campaign_name = "TEST_" . time();
            $testRecord->tenant_id = $tenantId;
            $testRecord->amount_spent = 1;
            $testRecord->account_name = $accountName;
            $testRecord->kategori_produk = $kategoriProduk;
            $testRecord->pic = $pic;
            
            $saved = $testRecord->save();
            \Log::info("Test record saved: " . ($saved ? 'yes' : 'no') . " with ID: " . $testRecord->id);
            
            // Delete the test record
            $testRecord->delete();
            \Log::info("Test record deleted");
        } catch (\Exception $e) {
            \Log::error("Error saving test record: " . $e->getMessage());
            // Continue with the import despite the test error
        }
        
        foreach ($dataRows as $rowIndex => $row) {
            // Skip empty rows
            $campaignName = $getColumnValue($row, 'Campaign name');
            if (empty($campaignName)) {
                continue;
            }
            
            try {
                \Log::info("Processing row " . ($rowIndex + $headerRow + 2) . " with campaign: " . $campaignName);
                
                // Map columns according to the provided sample data using the safe getter
                $primaryStatus = $getColumnValue($row, 'Primary status');
                $campaignBudget = $getColumnValue($row, 'Campaign Budget', 0);
                $amountSpent = $getColumnValue($row, 'Cost', 0);
                $impressions = $getColumnValue($row, 'Impressions', 0);
                $linkClicks = $getColumnValue($row, 'Clicks (destination)', 0);
                $productPageViews = $getColumnValue($row, 'Product page views (Shop)', 0);
                $addsToCartSharedItems = $getColumnValue($row, 'Checkouts initiated (Shop)', 0);
                $purchasesSharedItems = $getColumnValue($row, 'Purchases (Shop)', 0);
                $itemsPurchased = $getColumnValue($row, 'Items purchased (Shop)', 0);
                $purchasesConversionValueSharedItems = $getColumnValue($row, 'Gross revenue (Shop)', 0);
                $cpm = $getColumnValue($row, 'CPM', 0);
                $cpc = $getColumnValue($row, 'CPC (destination)', 0);
                $costPerPurchase = $getColumnValue($row, 'Cost per purchase (Shop)', 0);
                $ctr = $this->parsePercentage($getColumnValue($row, 'CTR (destination)', '0%'));
                $purchaseRate = $this->parsePercentage($getColumnValue($row, 'Purchase rate (Shop)', '0%'));
                $averageOrderValue = $getColumnValue($row, 'Average order value (Shop)', 0);
                $contentViewsSharedItems = $getColumnValue($row, 'Video views', 0);
                $liveViews = $getColumnValue($row, 'LIVE views', 0);
                
                // Remove currency symbols and commas from numeric values
                $amountSpent = $this->cleanNumericValue($amountSpent);
                $campaignBudget = $this->cleanNumericValue($campaignBudget);
                $impressions = $this->cleanNumericValue($impressions);
                $linkClicks = $this->cleanNumericValue($linkClicks);
                $productPageViews = $this->cleanNumericValue($productPageViews);
                $addsToCartSharedItems = $this->cleanNumericValue($addsToCartSharedItems);
                $purchasesSharedItems = $this->cleanNumericValue($purchasesSharedItems);
                $itemsPurchased = $this->cleanNumericValue($itemsPurchased);
                $purchasesConversionValueSharedItems = $this->cleanNumericValue($purchasesConversionValueSharedItems);
                $cpm = $this->cleanNumericValue($cpm);
                $cpc = $this->cleanNumericValue($cpc);
                $costPerPurchase = $this->cleanNumericValue($costPerPurchase);
                $averageOrderValue = $this->cleanNumericValue($averageOrderValue);
                $contentViewsSharedItems = $this->cleanNumericValue($contentViewsSharedItems);
                $liveViews = $this->cleanNumericValue($liveViews);

                // Check if record already exists
                $existingRecord = AdsTiktok::where([
                    'date' => $reportDate,
                    'campaign_name' => $campaignName,
                    'tenant_id' => $tenantId
                ])->first();
                
                if ($existingRecord) {
                    \Log::info("Record already exists with ID: " . $existingRecord->id . ", updating...");
                }
                
                // Prepare data for saving
                $saveData = [
                    'amount_spent' => (int)$amountSpent,
                    'impressions' => (int)$impressions,
                    'link_clicks' => (float)$linkClicks,
                    'content_views_shared_items' => (float)$contentViewsSharedItems,
                    'adds_to_cart_shared_items' => (float)$addsToCartSharedItems,
                    'purchases_shared_items' => (float)$purchasesSharedItems,
                    'purchases_conversion_value_shared_items' => (float)$purchasesConversionValueSharedItems,
                    'account_name' => $accountName,
                    'kategori_produk' => $kategoriProduk,
                    'pic' => $pic,
                    'primary_status' => $primaryStatus,
                    'campaign_budget' => (float)$campaignBudget,
                    'product_page_views' => (int)$productPageViews,
                    'items_purchased' => (int)$itemsPurchased,
                    'cpm' => (float)$cpm,
                    'cpc' => (float)$cpc,
                    'cost_per_purchase' => (float)$costPerPurchase,
                    'ctr' => (float)$ctr,
                    'purchase_rate' => (float)$purchaseRate,
                    'average_order_value' => (float)$averageOrderValue,
                    'live_views' => (int)$liveViews
                ];
                
                \Log::info("Data to be saved: " . json_encode(array_slice($saveData, 0, 5)) . "...");
                
                // Try direct insert instead of updateOrCreate for testing
                try {
                    $model = new AdsTiktok();
                    $model->date = $reportDate;
                    $model->campaign_name = $campaignName;
                    $model->tenant_id = $tenantId;
                    
                    foreach ($saveData as $key => $value) {
                        $model->$key = $value;
                    }
                    
                    $saved = $model->save();
                    \Log::info("Direct insert result: " . ($saved ? 'success' : 'failed') . " for ID: " . $model->id);
                } catch (\Exception $e) {
                    \Log::error("Error with direct insert: " . $e->getMessage());
                    
                    // Fall back to updateOrCreate
                    try {
                        $result = AdsTiktok::updateOrCreate(
                            [
                                'date' => $reportDate,
                                'campaign_name' => $campaignName,
                                'tenant_id' => $tenantId
                            ],
                            $saveData
                        );
                        \Log::info("updateOrCreate result ID: " . $result->id);
                    } catch (\Exception $e2) {
                        \Log::error("Error with updateOrCreate: " . $e2->getMessage());
                        throw $e2;
                    }
                }
                
                if (!isset($dateAmountMap[$reportDate])) {
                    $dateAmountMap[$reportDate] = 0;
                }
                $dateAmountMap[$reportDate] += (int)$amountSpent;
                
                $count++;
            } catch (\Exception $e) {
                \Log::warning("Error processing row in XLSX: Row " . ($rowIndex + $headerRow + 2) . " - " . $e->getMessage());
                // Continue processing other rows
            }
        }
        
        \Log::info("Finished processing file. Imported " . $count . " records.");
        return $count;
    }

    /**
     * Parse percentage string to decimal
     * 
     * @param string $percentStr
     * @return float
     */
    private function parsePercentage($percentStr)
    {
        if (is_numeric($percentStr)) {
            return $percentStr;
        }
        
        // Remove % symbol and convert comma to dot
        $percentStr = str_replace('%', '', $percentStr);
        $percentStr = str_replace(',', '.', $percentStr);
        
        // Convert to float and divide by 100
        $result = floatval($percentStr) / 100;
        
        return $result;
    }

    /**
     * Clean numeric value by removing currency symbols and formatting
     * 
     * @param mixed $value
     * @return string
     */
    private function cleanNumericValue($value)
    {
        if (is_numeric($value)) {
            return $value;
        }
        
        // Convert to string
        $value = (string)$value;
        
        // Remove currency symbols, commas, spaces
        $value = preg_replace('/[^\d.-]/', '', $value);
        
        return $value ?: '0';
    }




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
    
    private function determinePIC($accountName)
    {
        // Regular expressions for matching account names
        if (preg_match('/---REZA(-|---|$|\s|&)/i', $accountName) || 
            preg_match('/REZA---/i', $accountName) ||
            preg_match('/---JB---REZA/i', $accountName) ||
            preg_match('/---GS---REZA/i', $accountName)) {
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
            preg_match('/CPAS-CLEORA-JAKARTA/i', $accountName) ||
            strpos($accountName, 'IPO2024---AI-YASYFI-YUTIMNA-HKU---3') !== false ||
            strpos($accountName, 'MKH---CLEORA-6') !== false) {
            return 'LABIB';
        }
        
        if (preg_match('/---ANGGA(-|---|$|\s)/i', $accountName) || 
            preg_match('/ANGGA---/i', $accountName) ||
            strpos($accountName, 'IPO2024---AGUS-HKU---') !== false ||
            strpos($accountName, 'CPAS---CLEORA---ANGGA') !== false ||
            strpos($accountName, 'IPO2024---AI-YASYFI-YUTIMNA-HKU---2') !== false) {
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

        $tenant_id = auth()->user()->current_tenant_id;
        
        // Define twin dates to exclude (where day and month are the same)
        $excludeDates = [];
        
        // Get all dates in the range
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $day = $currentDate->format('d');
            $month = $currentDate->format('m');
            
            // If day equals month, it's a twin date (e.g., 05-05, 04-04)
            if ($day == $month) {
                $excludeDates[] = $currentDate->format('Y-m-d');
            }
            
            $currentDate->addDay();
        }
        
        // Build the query for funnel totals
        $funnelQuery = AdsMeta::where('tenant_id', $tenant_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
        // Exclude twin dates if any exist in our range
        if (!empty($excludeDates)) {
            $funnelQuery->whereNotIn('date', $excludeDates);
        }
        
        // Apply filters if provided
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $funnelQuery->where('kategori_produk', $request->kategori_produk);
        }

        if ($request->has('pic') && $request->pic !== '') {
            $funnelQuery->where('pic', $request->pic);
        }
        
        // Get funnel data
        $data = $funnelQuery->select(
            DB::raw('SUM(impressions) as total_impressions'),
            DB::raw('SUM(content_views_shared_items) as total_content_views'),
            DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
            DB::raw('SUM(purchases_shared_items) as total_purchases')
        )->first();

        // Find the date with minimum amount spent
        $dailySpendQuery = clone $funnelQuery;
        $dailySpends = $dailySpendQuery
            ->select('date', DB::raw('SUM(amount_spent) as daily_spent'))
            ->whereNotNull('amount_spent')
            ->where('amount_spent', '>', 0)
            ->groupBy('date')
            ->orderBy('daily_spent', 'asc') // Sort by ascending spent to get min
            ->limit(1)
            ->get();
        
        // Find min spent day
        $minSpent = $dailySpends->first();

        // Find the date with maximum adds to cart
        $dailyAtcQuery = clone $funnelQuery;
        $dailyAtcs = $dailyAtcQuery
            ->select('date', DB::raw('SUM(adds_to_cart_shared_items) as daily_atc'))
            ->whereNotNull('adds_to_cart_shared_items')
            ->where('adds_to_cart_shared_items', '>', 0)
            ->groupBy('date')
            ->orderBy('daily_atc', 'desc') // Sort by descending ATC to get max
            ->limit(1)
            ->get();
        
        // Find max ATC day
        $maxAtc = $dailyAtcs->first();

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
            ],
            'min_spent' => [
                'date' => $minSpent ? Carbon::parse($minSpent->date)->format('d M Y') : null,
                'value' => $minSpent ? (int)$minSpent->daily_spent : 0
            ],
            'max_atc' => [
                'date' => $maxAtc ? Carbon::parse($maxAtc->date)->format('d M Y') : null,
                'value' => $maxAtc ? (float)$maxAtc->daily_atc : 0
            ],
            'excluded_twin_dates' => $excludeDates
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
