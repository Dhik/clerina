<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\AdsMeta;
use App\Domain\Sales\Models\AdsMonitoring;
use App\Domain\Sales\Models\AdsShopee;
use App\Domain\Sales\Models\AdsMeta2;
use App\Domain\Sales\Models\AdsMeta3;
use Illuminate\Support\Facades\Validator;
use App\Domain\Sales\Models\AdsTiktok;
use App\Domain\Sales\Requests\AdSpentSocialMediaRequest;
use App\Http\Controllers\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Auth;
use Exception;
use Carbon\Carbon; 
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use App\Domain\Sales\Services\GoogleSheetService;
use Illuminate\Support\Facades\Storage;


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
    public function get_tiktok_details_by_date(Request $request) 
    {
        $query = AdsTiktok::query()
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
            ->addColumn('tofu_spent', function ($row) {
                return $row->tofu_spent ? 'Rp ' . number_format($row->tofu_spent, 0, ',', '.') : '-';
            })
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
    public function get_tiktok_campaign_summary(Request $request)
    {
        $query = AdsTiktok::query()
            ->select([
                DB::raw('SUM(amount_spent) as total_amount_spent'),
                DB::raw('SUM(impressions) as total_impressions'),
                DB::raw('SUM(link_clicks) as total_link_clicks'),
                DB::raw('SUM(content_views_shared_items) as total_content_views'),
                DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
                DB::raw('SUM(purchases_shared_items) as total_purchases'),
                DB::raw('SUM(purchases_conversion_value_shared_items) as total_conversion_value'),
                DB::raw('COUNT(DISTINCT account_name) as accounts_count'),
                
                // TOFU/MOFU/BOFU metrics
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%TOFU%" THEN amount_spent ELSE 0 END) as tofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%MOFU%" THEN amount_spent ELSE 0 END) as mofu_spent'),
                DB::raw('SUM(CASE WHEN campaign_name LIKE "%BOFU%" THEN amount_spent ELSE 0 END) as bofu_spent')
            ]);
        
        // Apply date filter with support for both single date and date range
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
            
            // Funnel stage metrics with percentages
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

    public function deleteTiktokByAccountAndDate(Request $request)
    {
        $accountName = $request->input('account_name');
        $date = $request->input('date');
        
        try {
            $deleted = AdsTiktok::where('account_name', $accountName)
                ->where('date', $date)
                ->delete();
            
            if ($deleted) {
                return response()->json([
                    'status' => 'success',
                    'message' => "Successfully deleted data for '{$accountName}' on {$date}"
                ]);
            } else {
                // Collect debug info for troubleshooting
                $availableAccounts = AdsTiktok::where('date', $date)
                    ->pluck('account_name')
                    ->toArray();
                
                return response()->json([
                    'status' => 'error',
                    'message' => "No data found for '{$accountName}' on {$date}",
                    'debug_info' => [
                        'requested_account' => $accountName,
                        'requested_date' => $date,
                        'available_accounts' => $availableAccounts
                    ]
                ], 404);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => "Error deleting data: " . $e->getMessage()
            ], 500);
        }
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
        
        // Determine sku_induk based on kategori_produk
        $skuInduk = $this->determineSkuInduk($kategoriProduk);
        
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
                        'pic' => $pic,
                        'sku_induk' => $skuInduk
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
    private function determineSkuInduk($kategoriProduk)
    {
        switch ($kategoriProduk) {
            case 'Red Saviour':
                return 'CLE-RS-047';
            case 'Jelly Booster':
                return 'CLE-JB30-001';
            case 'Glowsmooth':
                return 'CL-GS';
            case '3 Minutes':
                return 'CLE-XFO-008';
            case 'Calendula':
                return 'CLE-CLNDLA-025';
            case 'Natural Exfo':
                return 'CLE-NEG-071';
            case 'Pore Glow':
                return 'CL-TNR';
            case '8X Hyalu':
                return 'CL-8XHL';
            case 'Lain-lain':
                return '-';
            default:
                return null;
        }
    }
    public function exportAdsMetaStats()
    {
        $newSpreadsheetId = '17Sls8V-UH5gWobRCqxdeI8IySx7a7Nf7HGs3cDUI2F8';

        if ($newSpreadsheetId) {
            $this->googleSheetService->setSpreadsheetId($newSpreadsheetId);
        }
        
        $startDate = '2025-05-01';
        $endDate = '2025-05-18';
        
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
        $reportDate = null;
        $accountName = null;
        
        // Pattern 1: "Cleora 10 - VSA - Labib-Campaign Report(2025-04-01 to 2025-04-01)"
        if (preg_match('/(.+)-Campaign Report\((\d{4})-(\d{2})-(\d{2}) to/', $filename, $matches)) {
            $accountName = trim($matches[1]);
            $reportDate = $matches[2] . '-' . $matches[3] . '-' . $matches[4];
            \Log::info("Pattern 1: Extracted date from filename: " . $reportDate);
            \Log::info("Pattern 1: Extracted account name: " . $accountName);
        }
        // Pattern 2: "Cleora_8_Mofu_Bofu_Nabilah_Campaign_Report2025_05_13_to_2025_05"
        else if (preg_match('/(.+)_Campaign_Report(\d{4})_(\d{2})_(\d{2})/', $filename, $matches)) {
            $accountName = $matches[1];
            $reportDate = $matches[2] . '-' . $matches[3] . '-' . $matches[4];
            \Log::info("Pattern 2: Extracted date from filename: " . $reportDate);
            \Log::info("Pattern 2: Extracted account name: " . $accountName);
        } else {
            $accountName = $filename;
            $reportDate = Carbon::now()->format('Y-m-d');
            \Log::info("No pattern matched. Using default date: " . $reportDate . " and account name: " . $accountName);
        }
        
        // Use Excel to read the file with fallback options
        try {
            // Try simple Excel read first
            $data = Excel::toArray(new class {} , $filePath)[0];
            
            \Log::info("Excel file loaded successfully with " . count($data) . " rows");
            
            // For debugging, log the first few rows
            for ($i = 0; $i < min(3, count($data)); $i++) {
                \Log::info("Row $i: " . json_encode(array_slice($data[$i], 0, 5)) . "...");
            }
        } catch (\Exception $e) {
            \Log::error("Error loading Excel file: " . $e->getMessage());
            
            // Try another approach with PhpSpreadsheet directly
            try {
                \Log::info("Trying PhpSpreadsheet directly as fallback");
                $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
                $spreadsheet = $reader->load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $data = $worksheet->toArray();
                \Log::info("PhpSpreadsheet fallback successful with " . count($data) . " rows");
            } catch (\Exception $e2) {
                \Log::error("PhpSpreadsheet fallback also failed: " . $e2->getMessage());
                return 0;
            }
        }
        
        // Find the header row and map column indices
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
        $skippedCount = 0; // Track skipped rows
        
        // Create a function to safely get column values
        $getColumnValue = function($row, $columnName, $default = null) use ($columnMap) {
            return isset($columnMap[$columnName]) && isset($row[$columnMap[$columnName]]) 
                ? $row[$columnMap[$columnName]] 
                : $default;
        };
        
        foreach ($dataRows as $rowIndex => $row) {
            // Skip empty rows
            $campaignName = $getColumnValue($row, 'Campaign name');
            if (empty($campaignName)) {
                continue;
            }
            
            // EXCLUDE ROWS WHERE COLUMN B (index 1) HAS VALUE "-"
            if (isset($row[1]) && trim($row[1]) === '-') {
                \Log::info("Skipping row " . ($rowIndex + $headerRow + 2) . " because column B contains '-'");
                $skippedCount++;
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

                // Prepare data for saving with all columns
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
                    'type' => 'Ads Manager',
                    'live_views' => (int)$liveViews
                ];
                
                \Log::info("Data to be saved: " . json_encode(array_slice($saveData, 0, 5)) . "...");
                
                try {
                    $result = AdsTiktok::updateOrCreate(
                        [
                            'date' => $reportDate,
                            'campaign_name' => $campaignName,
                            'account_name' => $accountName,
                            'content_views_shared_items' => (float)$contentViewsSharedItems,
                            'tenant_id' => $tenantId,
                            'amount_spent' => (int)$amountSpent,
                        ],
                        $saveData
                    );
                    
                    \Log::info("Record saved with ID: " . $result->id);
                    
                    if (!isset($dateAmountMap[$reportDate])) {
                        $dateAmountMap[$reportDate] = 0;
                    }
                    $dateAmountMap[$reportDate] += (int)$amountSpent;
                    
                    $count++;
                } catch (\Exception $e) {
                    \Log::error("Error saving record: " . $e->getMessage());
                }
            } catch (\Exception $e) {
                \Log::warning("Error processing row in XLSX: Row " . ($rowIndex + $headerRow + 2) . " - " . $e->getMessage());
                // Continue processing other rows
            }
        }
        
        \Log::info("Finished processing file. Imported " . $count . " records. Skipped " . $skippedCount . " rows with '-' in column B.");
        return $count;
    }

    public function import_tiktok_gmv_max(Request $request)
    {
        try {
            $request->validate([
                'tiktokGmvMaxFile' => 'required|file|mimes:xlsx,csv,zip|max:5120',
                'kategori_produk' => 'required|string',
                'pic' => 'required|string'
            ]);

            $file = $request->file('tiktokGmvMaxFile');
            $kategoriProduk = $request->input('kategori_produk');
            $pic = $request->input('pic');
            $importCount = 0;
            $dateAmountMap = [];
            
            \Log::info("Starting TikTok GMV Max import process");
            \Log::info("File name: " . $file->getClientOriginalName());
            \Log::info("Kategori produk: " . $kategoriProduk);
            \Log::info("PIC: " . $pic);

            // Capturing tenant ID early to ensure it's available
            $tenantId = Auth::user()->current_tenant_id;
            \Log::info("Using tenant ID: " . $tenantId);

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
                            $processResult = $this->processGmvMaxExcelFile($xlsxFile, $xlsxFilename, $kategoriProduk, $pic, $tenantId);
                            $importCount += $processResult['count'];
                            
                            // Merge date-amount maps
                            foreach ($processResult['dateAmountMap'] as $date => $amount) {
                                if (isset($dateAmountMap[$date])) {
                                    $dateAmountMap[$date] += $amount;
                                } else {
                                    $dateAmountMap[$date] = $amount;
                                }
                            }
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
                    $processResult = $this->processGmvMaxExcelFile($file->getPathname(), $originalFilename, $kategoriProduk, $pic, $tenantId);
                    $importCount = $processResult['count'];
                    $dateAmountMap = $processResult['dateAmountMap'];
                }

                // Update AdSpentSocialMedia with aggregated totals
                foreach ($dateAmountMap as $date => $totalAmount) {
                    \Log::info("Updating AdSpentSocialMedia for date: " . $date . " with amount: " . $totalAmount);
                    
                    try {
                        // Get all existing amount for this date
                        $existingAmount = AdSpentSocialMedia::where('date', $date)
                            ->where('social_media_id', 4) // Assuming 4 is for TikTok
                            ->where('tenant_id', $tenantId)
                            ->value('amount') ?? 0;
                        
                        AdSpentSocialMedia::updateOrCreate(
                            [
                                'date' => $date,
                                'social_media_id' => 4, // Assuming 4 is for TikTok
                                'tenant_id' => $tenantId
                            ],
                            [
                                'amount' => $existingAmount + $totalAmount
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
                    'message' => 'TikTok GMV Max data imported successfully. ' . $importCount . ' records imported.'
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                \Log::error("Import failed with error: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                throw $e;
            }
            
        } catch (\Exception $e) {
            \Log::error("Error in import_tiktok_gmv_max: " . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing data: ' . $e->getMessage()
            ], 422);
        }
    }

    /**
     * Process a single GMV Max Excel file
     * 
     * @param string $filePath The path to the Excel file
     * @param string $filename The original filename
     * @param string $kategoriProduk The product category
     * @param string $pic The person in charge
     * @param int $tenantId The tenant ID
     * @return array Returns an array with count of imported records and date-amount map
     */
    private function processGmvMaxExcelFile($filePath, $filename, $kategoriProduk, $pic, $tenantId)
    {
        $importCount = 0;
        $dateAmountMap = [];
        $skippedCount = 0;
        
        // Extract date from filename
        $importDate = null;
        
        // Pattern to match date formats like "2025-05-12" in the filename
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $filename, $matches)) {
            $importDate = $matches[1];
            // Validate the extracted date
            if (!$this->validateDate($importDate)) {
                throw new \Exception("Invalid date format extracted from filename: $importDate");
            }
        } else {
            throw new \Exception("Unable to extract date from filename '$filename'. Please ensure filename contains date in YYYY-MM-DD format.");
        }
        
        \Log::info("Extracted date from filename: " . $importDate);
        
        $spreadsheet = IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // DEBUG: Log the first few rows to see what we're working with
        \Log::info("Total rows in Excel: " . count($rows));
        for ($i = 0; $i < min(3, count($rows)); $i++) {
            \Log::info("Row $i: " . json_encode(array_slice($rows[$i], 0, 5)));
        }
        
        // Find the actual header row and remove everything above it
        $headerRowIndex = null;
        $dataStartIndex = 0;
        
        // Look for header indicators (adjust these based on your actual headers)
        $expectedHeaders = ['id', 'campaign', 'amount', 'biaya', 'spent', 'cost'];
        
        foreach ($rows as $index => $row) {
            if (empty($row) || count(array_filter($row)) == 0) {
                // Skip completely empty rows
                continue;
            }
            
            // Check if this row looks like a header
            $isHeaderRow = false;
            foreach ($row as $cell) {
                if (is_string($cell)) {
                    $cellLower = strtolower(trim($cell));
                    foreach ($expectedHeaders as $header) {
                        if (strpos($cellLower, $header) !== false) {
                            $isHeaderRow = true;
                            break 2;
                        }
                    }
                }
            }
            
            if ($isHeaderRow) {
                $headerRowIndex = $index;
                $dataStartIndex = $index + 1;
                \Log::info("Found header row at index $index: " . json_encode(array_slice($row, 0, 5)));
                break;
            }
            
            // If we haven't found a header and this row has data, assume it's the first data row
            if ($headerRowIndex === null && !empty($row[0])) {
                $dataStartIndex = $index;
                \Log::info("No clear header found, starting data from row $index");
                break;
            }
        }
        
        // Get only the data rows
        $dataRows = array_slice($rows, $dataStartIndex);
        \Log::info("Processing " . count($dataRows) . " data rows starting from index $dataStartIndex");
        
        $totalAmountForDate = 0;
        
        foreach ($dataRows as $rowIndex => $row) {
            $actualRowNumber = $dataStartIndex + $rowIndex + 1; // +1 for Excel row numbering
            
            \Log::info("=== PROCESSING ROW $actualRowNumber ===");
            \Log::info("Raw row data: " . json_encode($row));
            \Log::info("Row count: " . count($row));
            \Log::info("Row[0] (id_campaign): '" . ($row[0] ?? 'NULL') . "'");
            \Log::info("Row[1] (campaign_name): '" . ($row[1] ?? 'NULL') . "'");
            \Log::info("Row[2] (amount_spent): '" . ($row[2] ?? 'NULL') . "'");
            \Log::info("Row[3] (biaya_bersih): '" . ($row[3] ?? 'NULL') . "'");
            
            // Check if we have the minimum required fields
            if (count($row) < 4) {
                \Log::info("SKIPPED: Row $actualRowNumber has less than 4 columns (" . count($row) . " columns)");
                continue;
            }
            
            if (empty($row[0])) {
                \Log::info("SKIPPED: Row $actualRowNumber has empty row[0] (id_campaign)");
                continue;
            }
            
            // EXCLUDE ROWS WHERE COLUMN B (campaign_name) HAS VALUE "-"
            if (isset($row[1]) && trim($row[1]) === '-') {
                \Log::info("SKIPPED: Row $actualRowNumber because column B (campaign_name) contains '-'");
                $skippedCount++;
                continue;
            }
            
            // Map columns according to your specification:
            $idCampaign = trim($row[0]);              // Column A -> id_campaign
            $campaignName = trim($row[1]);            // Column B -> campaign_name  
            $amountSpent = $this->cleanNumericValue($row[2]); // Column C -> amount_spent
            $biayaBersih = $this->cleanNumericValue($row[3]); // Column D -> biaya_bersih
            
            \Log::info("Mapped values:");
            \Log::info("- idCampaign: '$idCampaign'");
            \Log::info("- campaignName: '$campaignName'");
            \Log::info("- amountSpent: $amountSpent");
            \Log::info("- biayaBersih: $biayaBersih");
            
            // Optional columns (if they exist in the file)
            $itemsPurchased = isset($row[4]) ? $this->cleanNumericValue($row[4]) : 0;
            $costPerPurchase = isset($row[5]) ? $this->cleanNumericValue($row[5]) : 0;
            $conversionValue = isset($row[6]) ? $this->cleanNumericValue($row[6]) : 0;
            $roi = isset($row[7]) ? $this->cleanNumericValue($row[7]) : 0;
            $mataUang = isset($row[8]) && !empty($row[8]) ? trim($row[8]) : 'IDR';
            
            // Skip row if critical data is invalid
            if (empty($campaignName)) {
                \Log::warning("SKIPPED: Row $actualRowNumber due to empty campaign name after trim");
                $skippedCount++;
                continue;
            }
            
            // Additional default fields
            $accountName = "GMV Max";
            $type = "GMV Max";
            
            // Calculate purchases_shared_items based on items_purchased or cost_per_purchase
            $purchasesSharedItems = 0;
            if ($costPerPurchase > 0 && $amountSpent > 0) {
                $purchasesSharedItems = $amountSpent / $costPerPurchase;
            } elseif ($itemsPurchased > 0) {
                $purchasesSharedItems = $itemsPurchased;
            }
            
            \Log::info("Processing row $actualRowNumber: $idCampaign - $campaignName (Amount: $amountSpent, Biaya Bersih: $biayaBersih)");
            
            try {
                // Check if record already exists
                $existingRecord = AdsTiktok::where('date', $importDate)
                    ->where('id_campaign', $idCampaign)
                    ->where('account_name', $accountName)
                    ->where('tenant_id', $tenantId)
                    ->first();
                
                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update([
                        'campaign_name' => $campaignName,
                        'amount_spent' => $amountSpent,
                        'biaya_bersih' => $biayaBersih,
                        'items_purchased' => $itemsPurchased,
                        'cost_per_purchase' => $costPerPurchase,
                        'purchases_conversion_value_shared_items' => $conversionValue,
                        'purchases_shared_items' => $purchasesSharedItems,
                        'roi' => $roi,
                        'mata_uang' => $mataUang,
                        'type' => $type,
                        'kategori_produk' => $kategoriProduk,
                        'pic' => $pic,
                        'updated_at' => now()
                    ]);
                    
                    \Log::info("Updated existing record for campaign: $idCampaign");
                } else {
                    // Create new record
                    AdsTiktok::create([
                        'date' => $importDate,
                        'id_campaign' => $idCampaign,
                        'campaign_name' => $campaignName,
                        'account_name' => $accountName,
                        'amount_spent' => $amountSpent,
                        'biaya_bersih' => $biayaBersih,
                        'items_purchased' => $itemsPurchased,
                        'cost_per_purchase' => $costPerPurchase,
                        'purchases_conversion_value_shared_items' => $conversionValue,
                        'purchases_shared_items' => $purchasesSharedItems,
                        'roi' => $roi,
                        'mata_uang' => $mataUang,
                        'type' => $type,
                        'kategori_produk' => $kategoriProduk,
                        'pic' => $pic,
                        'tenant_id' => $tenantId,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                    
                    \Log::info("Created new record for campaign: $idCampaign");
                }
                
                $importCount++;
                $totalAmountForDate += $amountSpent;
                
            } catch (\Exception $e) {
                \Log::error("Error processing row $actualRowNumber: " . $e->getMessage());
                $skippedCount++;
                continue;
            }
        }
        
        if ($importCount > 0) {
            $dateAmountMap[$importDate] = $totalAmountForDate;
        }
        
        \Log::info("Finished processing GMV Max file. Imported: $importCount, Skipped: $skippedCount records");
        
        return [
            'count' => $importCount,
            'dateAmountMap' => $dateAmountMap
        ];
    }

    // Helper method to validate date format
    private function validateDate($date, $format = 'Y-m-d')
    {
        $d = \DateTime::createFromFormat($format, $date);
        return $d && $d->format($format) === $date;
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
        
        // Build the base query with all common filters
        $baseQuery = AdsMeta::where('tenant_id', $tenant_id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
        // Apply common filters
        if ($request->has('kategori_produk') && $request->kategori_produk !== '') {
            $baseQuery->where('kategori_produk', $request->kategori_produk);
        }

        if ($request->has('pic') && $request->pic !== '') {
            $baseQuery->where('pic', $request->pic);
        }

        // Clone the base query for metrics that exclude twin dates
        $funnelQuery = clone $baseQuery;
        
        // Exclude twin dates if any exist in our range (only for the funnel metrics)
        if (!empty($excludeDates)) {
            $funnelQuery->whereNotIn('date', $excludeDates);
        }
        
        // Get funnel data (excluding twin dates)
        $data = $funnelQuery->select(
            DB::raw('SUM(impressions) as total_impressions'),
            DB::raw('SUM(content_views_shared_items) as total_content_views'),
            DB::raw('SUM(adds_to_cart_shared_items) as total_adds_to_cart'),
            DB::raw('SUM(purchases_shared_items) as total_purchases')
        )->first();

        // Get link clicks data (including twin dates)
        $linkClicksData = $baseQuery->select(
            DB::raw('SUM(link_clicks) as total_link_clicks')
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
                    'name' => 'Link Clicks',
                    'value' => (float)($linkClicksData->total_link_clicks ?? 0)
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
    /**
     * Get Shopee Ads data for DataTables
     */
    public function get_ads_shopee(Request $request)
    {
        $query = AdsShopee::query()
            ->select([
                'date',
                DB::raw('SUM(dilihat) as total_dilihat'),
                DB::raw('SUM(jumlah_klik) as total_jumlah_klik'),
                DB::raw('SUM(konversi) as total_konversi'),
                DB::raw('SUM(produk_terjual) as total_produk_terjual'),
                DB::raw('SUM(omzet_penjualan) as total_omzet_penjualan'),
                DB::raw('SUM(biaya) as total_biaya'),
                DB::raw('AVG(efektivitas_iklan) as avg_efektivitas_iklan'),
            ])
            ->groupBy('date'); // Only group by date, removing kode_produk
        
        // Apply filters
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        if ($request->has('mode_bidding') && $request->mode_bidding) {
            $query->where('mode_bidding', $request->mode_bidding);
        }
        
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } else {
            $query->whereMonth('date', now()->month)
                ->whereYear('date', now()->year);
        }
        
        if ($request->has('kode_produk') && $request->kode_produk) {
            $query->where('kode_produk', $request->kode_produk);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return '<a href="javascript:void(0)" class="date-details" data-date="'.$row->date.'">' . 
                        Carbon::parse($row->date)->format('d M Y') . '</a>';
            })
            ->addColumn('ctr', function ($row) {
                if ($row->total_dilihat > 0 && $row->total_jumlah_klik > 0) {
                    return ($row->total_jumlah_klik / $row->total_dilihat) * 100;
                }
                return 0;
            })
            ->addColumn('conversion_rate', function ($row) {
                if ($row->total_jumlah_klik > 0 && $row->total_konversi > 0) {
                    return ($row->total_konversi / $row->total_jumlah_klik) * 100;
                }
                return 0;
            })
            ->addColumn('cost_per_click', function ($row) {
                if ($row->total_jumlah_klik > 0 && $row->total_biaya > 0) {
                    return $row->total_biaya / $row->total_jumlah_klik;
                }
                return 0;
            })
            ->addColumn('roas', function ($row) {
                if ($row->total_biaya > 0 && $row->total_omzet_penjualan > 0) {
                    return $row->total_omzet_penjualan / $row->total_biaya;
                }
                return 0;
            })
            ->addColumn('performance', function ($row) {
                if ($row->total_biaya > 0 && $row->total_omzet_penjualan > 0) {
                    $roas = $row->total_omzet_penjualan / $row->total_biaya;
                    
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

    public function get_shopee_details_by_date(Request $request)
    {
        $query = AdsShopee::query()
            ->select([
                'id',
                'date',
                'sku_induk',
                'dilihat',
                'suka',
                'jumlah_klik',
                'dimasukan_ke_keranjang_produk',
                'produk_pesanan_dibuat',
                'produk_pesanan_siap_dikirim',
                'produk_terjual',
                'omzet_penjualan',
                'biaya',
                'efektivitas_iklan'
            ]);
        
        if ($request->has('date_start') && $request->has('date_end')) {
            $query->whereBetween('date', [$request->date_start, $request->date_end]);
        } elseif ($request->has('date')) {
            $query->where('date', $request->date);
        }
        
        if ($request->has('kode_produk') && $request->kode_produk) {
            $query->where('kode_produk', $request->kode_produk);
        }
        if ($request->has('mode_bidding') && $request->mode_bidding) {
            $query->where('mode_bidding', $request->mode_bidding);
        }
        
        if (auth()->user()->tenant_id) {
            $query->where('tenant_id', auth()->user()->tenant_id);
        }
        
        return DataTables::of($query)
            ->addIndexColumn()
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('d M Y');
            })
            ->editColumn('dilihat', function ($row) {
                return number_format($row->dilihat, 0, ',', '.');
            })
            ->editColumn('suka', function ($row) {
                return number_format($row->suka, 0, ',', '.');
            })
            ->editColumn('jumlah_klik', function ($row) {
                return number_format($row->jumlah_klik, 0, ',', '.');
            })
            ->editColumn('dimasukan_ke_keranjang_produk', function ($row) {
                return number_format($row->dimasukan_ke_keranjang_produk, 0, ',', '.');
            })
            ->editColumn('produk_pesanan_dibuat', function ($row) {
                return number_format($row->produk_pesanan_dibuat, 0, ',', '.');
            })
            ->editColumn('produk_pesanan_siap_dikirim', function ($row) {
                return number_format($row->produk_pesanan_siap_dikirim, 0, ',', '.');
            })
            ->editColumn('produk_terjual', function ($row) {
                return number_format($row->produk_terjual, 0, ',', '.');
            })
            ->editColumn('omzet_penjualan', function ($row) {
                return 'Rp ' . number_format($row->omzet_penjualan, 0, ',', '.');
            })
            ->editColumn('biaya', function ($row) {
                return 'Rp ' . number_format($row->biaya, 0, ',', '.');
            })
            ->editColumn('efektivitas_iklan', function ($row) {
                return number_format($row->efektivitas_iklan, 2, ',', '.');
            })
            ->addColumn('ctr', function ($row) {
                if ($row->dilihat > 0 && $row->jumlah_klik > 0) {
                    $ctr = ($row->jumlah_klik / $row->dilihat) * 100;
                    return number_format($ctr, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('cr', function ($row) {
                if ($row->jumlah_klik > 0 && $row->produk_terjual > 0) {
                    $cr = ($row->produk_terjual / $row->jumlah_klik) * 100;
                    return number_format($cr, 2, ',', '.') . '%';
                }
                return '-';
            })
            ->addColumn('roas', function ($row) {
                if ($row->biaya > 0 && $row->omzet_penjualan > 0) {
                    $roas = $row->omzet_penjualan / $row->biaya;
                    return number_format($roas, 2, ',', '.');
                }
                return '-';
            })
            ->addColumn('performance', function ($row) {
                if ($row->biaya > 0 && $row->omzet_penjualan > 0) {
                    $roas = $row->omzet_penjualan / $row->biaya;
                    
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
            ->addColumn('action', function ($row) {
                return '<button type="button" class="btn btn-danger btn-sm delete-record" data-id="'.$row->id.'">
                    <i class="fas fa-trash"></i> Delete
                </button>';
            })
            ->rawColumns(['performance', 'action'])
            ->make(true);
    }

    /**
     * Get Shopee summary
     */
    public function get_shopee_summary(Request $request)
    {
        try {
            $query = AdsShopee::query();
            
            if ($request->has('date_start') && $request->has('date_end')) {
                $query->whereBetween('date', [$request->date_start, $request->date_end]);
            } elseif ($request->has('date')) {
                $query->where('date', $request->date);
            }
            
            if ($request->has('kode_produk') && $request->kode_produk) {
                $query->where('kode_produk', $request->kode_produk);
            }
            if ($request->has('mode_bidding') && $request->mode_bidding) {
                $query->where('mode_bidding', $request->mode_bidding);
            }
            
            if (auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
            
            $summary = [
                'total_ads' => $query->count(),
                'total_impressions' => $query->sum('dilihat'),
                'total_clicks' => $query->sum('jumlah_klik'),
                'total_conversions' => $query->sum('konversi'),
                'total_cost' => $query->sum('biaya'),
                'total_revenue' => $query->sum('omzet_penjualan'),
                'avg_ctr' => 0,
                'avg_conversion_rate' => 0,
                'avg_roas' => 0
            ];
            
            if ($summary['total_impressions'] > 0 && $summary['total_clicks'] > 0) {
                $summary['avg_ctr'] = ($summary['total_clicks'] / $summary['total_impressions']) * 100;
            }
            
            if ($summary['total_clicks'] > 0 && $summary['total_conversions'] > 0) {
                $summary['avg_conversion_rate'] = ($summary['total_conversions'] / $summary['total_clicks']) * 100;
            }
            
            if ($summary['total_cost'] > 0 && $summary['total_revenue'] > 0) {
                $summary['avg_roas'] = $summary['total_revenue'] / $summary['total_cost'];
            }
            
            return response()->json([
                'status' => 'success',
                'summary' => $summary
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get summary: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Import Shopee Ads from CSV
     */
    public function importShopeeAds(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'csv_file' => 'required|file|mimes:csv,txt|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // Get the original filename
            $file = $request->file('csv_file');
            $originalFilename = $file->getClientOriginalName();
            
            // Extract date from filename (as a fallback)
            $date = $this->extractDateFromFilename($originalFilename);
            Log::info("Extracted date from filename: {$date}");
            
            // Store the file temporarily
            $path = $file->storeAs('temp', 'shopee_ads_import.csv');
            $filePath = Storage::path($path);
            
            // Process the CSV data, passing the extracted date
            $processedData = $this->processShopeeCSV($filePath, $date);
            $totalRows = count($processedData);
            Log::info("Processed {$totalRows} rows from CSV");
            
            // Save processed data to database
            $inserted = $this->saveShopeeToDatabase($processedData);
            Log::info("Inserted {$inserted} records into database");
            
            // Delete the temporary file
            Storage::delete($path);
            
            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported {$inserted} records from the CSV file for date {$date}.",
                'debug_info' => [
                    'total_rows_processed' => $totalRows,
                    'records_inserted' => $inserted,
                    'extracted_date' => $date,
                    'filename' => $originalFilename
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error importing Shopee Ads CSV: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing CSV: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function extractDateFromFilename($filename)
    {
        // Default to today if no date found
        $defaultDate = Carbon::now()->format('Y-m-d');
        
        // Try to match standard date patterns in the filename
        if (preg_match('/(\d{2}[_\/]\d{2}[_\/]\d{4})/', $filename, $matches)) {
            $dateString = $matches[1]; // This could be DD_MM_YYYY or DD/MM/YYYY
            
            // Check which format we have
            if (strpos($dateString, '_') !== false) {
                try {
                    return Carbon::createFromFormat('d_m_Y', $dateString)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning("Failed to parse underscore date from filename: {$filename}. Using default date instead.");
                }
            } else if (strpos($dateString, '/') !== false) {
                try {
                    return Carbon::createFromFormat('d/m/Y', $dateString)->format('Y-m-d');
                } catch (\Exception $e) {
                    Log::warning("Failed to parse slash date from filename: {$filename}. Using default date instead.");
                }
            }
        }
        
        Log::warning("No date pattern found in filename: {$filename}. Using default date instead.");
        return $defaultDate;
    }

    /**
     * Process Shopee CSV data with enhanced debugging
     */
    private function processShopeeCSV($filePath, $fileDate = null)
    {
        $data = [];
        $headers = [];
        $row = 0;
        $processedRows = 0;
        $skippedRows = 0;
        $validRows = 0;
        $skipRows = 7; // Updated to skip 7 rows for the new format
        
        if (($handle = fopen($filePath, "r")) !== false) {
            // First, let's log all the metadata rows for reference
            $metadataLines = [];
            $lineCount = 0;
            rewind($handle);
            while (($lineData = fgetcsv($handle, 1000, ",")) !== false && $lineCount < $skipRows + 1) {
                $metadataLines[] = $lineData;
                $lineCount++;
            }
            Log::info("CSV Metadata (first {$skipRows} rows plus header):", $metadataLines);
            
            // Try to extract date from the "Periode" metadata if possible
            foreach ($metadataLines as $line) {
                if (isset($line[0]) && strpos($line[0], 'Periode') !== false && isset($line[1])) {
                    // Format expected: "Periode,29/04/2025 - 29/04/2025"
                    if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $line[1], $matches)) {
                        try {
                            $fileDate = Carbon::createFromFormat('d/m/Y', $matches[0])->format('Y-m-d');
                            Log::info("Extracted date from Periode metadata: {$fileDate}");
                        } catch (\Exception $e) {
                            Log::warning("Failed to parse date from Periode metadata: {$line[1]}");
                        }
                    }
                }
            }
            
            // Reset file pointer and process the file
            rewind($handle);
            
            // Skip the metadata rows
            for ($i = 0; $i < $skipRows; $i++) {
                fgetcsv($handle, 1000, ",");
                $row++;
            }
            
            // The next row should be the header
            $headers = fgetcsv($handle, 1000, ",");
            $row++;
            
            // Now process the actual data rows
            while (($rowData = fgetcsv($handle, 1000, ",")) !== false) {
                $processedRows++;
                
                // Check if row has enough data
                if (count($rowData) < 5) {
                    Log::warning("Row {$row} doesn't have enough columns. Skipping.", ['data' => $rowData]);
                    $skippedRows++;
                    $row++;
                    continue;
                }
                
                try {
                    // Updated column mapping for new CSV structure:
                    // A=urutan, B=nama_iklan, C=status, D=jenis_ads, E=kode_produk, F=tampilan_iklan, G=mode_bidding, H=penempatan_iklan, I=tanggal_mulai, J=tanggal_selesai, K=dilihat, L=jumlah_klik, N=konversi, T=produk_terjual, V=omzet_penjualan, X=biaya, Z=efektivitas_iklan
                    
                    // Get kode_produk from the CSV (column E - index 4)
                    $kodeProduct = !empty($rowData[4]) ? $rowData[4] : 'Unknown';
                    
                    // Use the date from the metadata/filename if provided
                    $date = $fileDate;
                    if (empty($date)) {
                        Log::warning("No date found for row {$row}. Skipping.", ['data' => $rowData]);
                        $skippedRows++;
                        $row++;
                        continue;
                    }
                    
                    // Creating a unique key for each ad
                    $adName = isset($rowData[1]) ? $rowData[1] : 'Unnamed';
                    $jenisAds = isset($rowData[3]) ? $rowData[3] : '';
                    $key = $date . '_' . $kodeProduct . '_' . md5($adName . $jenisAds);
                    
                    $validRows++;
                    
                    if (!isset($data[$key])) {
                        // Initialize with first row data
                        $data[$key] = [
                            'date' => $date,
                            'kode_produk' => $kodeProduct,
                            'urutan' => $this->parseNumeric(isset($rowData[0]) ? $rowData[0] : 0),
                            'nama_iklan' => $adName,
                            'status' => isset($rowData[2]) ? $rowData[2] : '',
                            'jenis_ads' => $jenisAds, // New field from column D
                            'tampilan_iklan' => isset($rowData[5]) ? $rowData[5] : '', // Column F
                            'mode_bidding' => isset($rowData[6]) ? $rowData[6] : '', // Column G
                            'penempatan_iklan' => isset($rowData[7]) ? $rowData[7] : '', // Column H
                            'tanggal_mulai' => isset($rowData[8]) ? $this->parseDate($rowData[8]) : null, // Column I
                            'tanggal_selesai' => isset($rowData[9]) ? $rowData[9] : '', // Column J
                            'dilihat' => $this->parseNumeric(isset($rowData[10]) ? $rowData[10] : 0), // Column K
                            'jumlah_klik' => $this->parseNumeric(isset($rowData[11]) ? $rowData[11] : 0), // Column L
                            'konversi' => $this->parseNumeric(isset($rowData[13]) ? $rowData[13] : 0), // Column N
                            'produk_terjual' => $this->parseNumeric(isset($rowData[19]) ? $rowData[19] : 0), // Column T
                            'omzet_penjualan' => $this->parseNumeric(isset($rowData[21]) ? $rowData[21] : 0), // Column V
                            'biaya' => $this->parseNumeric(isset($rowData[23]) ? $rowData[23] : 0), // Column X
                            'efektivitas_iklan' => (float) str_replace(',', '.', isset($rowData[25]) ? $rowData[25] : 0), // Column Z
                            'count' => 1,
                        ];
                    } else {
                        // For existing groups, update numeric values for sum
                        $data[$key]['dilihat'] += $this->parseNumeric(isset($rowData[10]) ? $rowData[10] : 0);
                        $data[$key]['jumlah_klik'] += $this->parseNumeric(isset($rowData[11]) ? $rowData[11] : 0);
                        $data[$key]['konversi'] += $this->parseNumeric(isset($rowData[13]) ? $rowData[13] : 0);
                        $data[$key]['produk_terjual'] += $this->parseNumeric(isset($rowData[19]) ? $rowData[19] : 0);
                        $data[$key]['omzet_penjualan'] += $this->parseNumeric(isset($rowData[21]) ? $rowData[21] : 0);
                        $data[$key]['biaya'] += $this->parseNumeric(isset($rowData[23]) ? $rowData[23] : 0);
                        $data[$key]['efektivitas_iklan'] += (float) str_replace(',', '.', isset($rowData[25]) ? $rowData[25] : 0);
                        $data[$key]['count']++;
                    }
                } catch (\Exception $e) {
                    Log::error("Error processing row {$row}: " . $e->getMessage(), ['data' => $rowData]);
                    $skippedRows++;
                }
                
                $row++;
            }
            fclose($handle);
        } else {
            Log::error("Failed to open CSV file: {$filePath}");
            throw new \Exception("Failed to open CSV file");
        }
        
        // Calculate average for efektivitas_iklan
        foreach ($data as $key => $value) {
            if ($value['count'] > 0) {
                $data[$key]['efektivitas_iklan'] = $value['efektivitas_iklan'] / $value['count'];
            }
            // Remove count field as it's not in the database
            unset($data[$key]['count']);
        }
        
        Log::info("CSV Processing Summary: Processed {$processedRows} rows, Valid {$validRows}, Skipped {$skippedRows}, Unique Records " . count($data));
        
        // Debug: log a sample of processed data if available
        if (count($data) > 0) {
            $sampleRecord = reset($data);
            Log::info("Sample processed record:", $sampleRecord);
        }
        
        return array_values($data);
    }

    /**
     * Save processed Shopee data to database
     */
    private function saveShopeeToDatabase($data)
    {
        $count = 0;
        $updated = 0;
        $failed = 0;
        
        foreach ($data as $row) {
            try {
                // Check if a record already exists (updated to include jenis_ads in uniqueness check)
                $existingRecord = AdsShopee::where('date', $row['date'])
                    ->where('kode_produk', $row['kode_produk'])
                    ->where('nama_iklan', $row['nama_iklan'])
                    ->where('jenis_ads', $row['jenis_ads'])
                    ->first();
                
                if ($existingRecord) {
                    // Update existing record
                    $existingRecord->update($row);
                    $updated++;
                } else {
                    // Create new record
                    AdsShopee::create($row);
                    $count++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to save record: " . $e->getMessage(), ['data' => $row]);
                $failed++;
            }
        }
        
        Log::info("Database insertion summary: Inserted {$count}, Updated {$updated}, Failed {$failed}");
        
        return $count;
    }

    /**
     * Delete Shopee Ads record
     */
    public function deleteShopeeRecord(Request $request)
    {
        try {
            $record = AdsShopee::findOrFail($request->id);
            $record->delete();
            
            return response()->json([
                'status' => 'success',
                'message' => 'Record deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to delete record: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Shopee Ads line chart data
     */
    public function getShopeeLineData(Request $request)
    {
        try {
            $query = AdsShopee::query()
                ->select([
                    'date',
                    DB::raw('SUM(dilihat) as impressions'),
                    DB::raw('SUM(jumlah_klik) as clicks'),
                    DB::raw('SUM(biaya) as spent'),
                    DB::raw('SUM(omzet_penjualan) as revenue')
                ])
                ->groupBy('date')
                ->orderBy('date');
            
            if ($request->has('date_start') && $request->has('date_end')) {
                $query->whereBetween('date', [$request->date_start, $request->date_end]);
            } else {
                $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
            }
            
            if ($request->has('kode_produk') && $request->kode_produk) {
                $query->where('kode_produk', $request->kode_produk);
            }
            if ($request->has('mode_bidding') && $request->mode_bidding) {
                $query->where('mode_bidding', $request->mode_bidding);
            }
            
            if (auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
            
            $data = $query->get();
            
            return response()->json([
                'status' => 'success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get chart data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get Shopee Ads funnel data
     */
    public function getShopeeFunnelData(Request $request)
    {
        try {
            $query = AdsShopee::query();
            
            if ($request->has('date_start') && $request->has('date_end')) {
                $query->whereBetween('date', [$request->date_start, $request->date_end]);
            } else {
                $query->whereMonth('date', now()->month)
                    ->whereYear('date', now()->year);
            }
            
            if ($request->has('kode_produk') && $request->kode_produk) {
                $query->where('kode_produk', $request->kode_produk);
            }
            if ($request->has('mode_bidding') && $request->mode_bidding) {
                $query->where('mode_bidding', $request->mode_bidding);
            }
            
            if (auth()->user()->tenant_id) {
                $query->where('tenant_id', auth()->user()->tenant_id);
            }
            
            $totals = $query->selectRaw('
                SUM(dilihat) as impressions,
                SUM(jumlah_klik) as clicks,
                SUM(konversi) as conversions,
                SUM(produk_terjual) as purchases,
                SUM(biaya) as cost,
                SUM(omzet_penjualan) as revenue
            ')->first();
            
            // Calculate funnel metrics
            $ctr = $totals->impressions > 0 ? ($totals->clicks / $totals->impressions) * 100 : 0;
            $conversionRate = $totals->clicks > 0 ? ($totals->conversions / $totals->clicks) * 100 : 0;
            $purchaseRate = $totals->conversions > 0 ? ($totals->purchases / $totals->conversions) * 100 : 0;
            $roas = $totals->cost > 0 ? $totals->revenue / $totals->cost : 0;
            
            $funnelData = [
                ['name' => 'Impressions', 'value' => $totals->impressions],
                ['name' => 'Clicks', 'value' => $totals->clicks],
                ['name' => 'Conversions', 'value' => $totals->conversions],
                ['name' => 'Purchases', 'value' => $totals->purchases]
            ];
            
            $metrics = [
                'ctr' => $ctr,
                'conversion_rate' => $conversionRate,
                'purchase_rate' => $purchaseRate,
                'cost' => $totals->cost,
                'revenue' => $totals->revenue,
                'roas' => $roas
            ];
            
            return response()->json([
                'status' => 'success',
                'data' => $funnelData,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get funnel data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parse numeric value from string
     */
    private function parseNumeric($value)
    {
        // Remove any non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.]/', '', $value);
        return $value === '' ? 0 : (int) $value;
    }

    /**
     * Parse date from string
     */
    private function parseDate($date)
    {
        if (empty($date)) {
            return null;
        }
        
        try {
            return Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }

    public function importShopeeSkuDetails(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'excel_file' => 'required|file|mimes:xlsx,xls|max:10240',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => $validator->errors()->first(),
            ], 422);
        }

        try {
            // Get the original filename
            $file = $request->file('excel_file');
            $originalFilename = $file->getClientOriginalName();
            
            // Extract date from filename (format: export_report.parentskudetail.YYYYMMDD_YYYYMMDD.xlsx)
            $date = $this->extractDateFromSkuFilename($originalFilename);
            Log::info("Extracted date from SKU Excel filename: {$date}");
            
            // Store the file temporarily
            $path = $file->storeAs('temp', 'shopee_sku_import.xlsx');
            $filePath = Storage::path($path);
            
            // Process the Excel file
            $processedData = $this->processShopeeSkuExcel($filePath, $date);
            $totalRows = count($processedData);
            Log::info("Processed {$totalRows} rows from Excel");
            
            // Save processed data to database
            $result = $this->saveShopeeSkuToDatabase($processedData);
            Log::info("SKU Details import summary: Inserted {$result['inserted']}, Updated {$result['updated']}");
            
            // Delete the temporary file
            Storage::delete($path);
            
            return response()->json([
                'status' => 'success',
                'message' => "Successfully imported SKU details: {$result['inserted']} new records, {$result['updated']} updated records for date {$date}.",
                'debug_info' => [
                    'total_rows_processed' => $totalRows,
                    'records_inserted' => $result['inserted'],
                    'records_updated' => $result['updated'],
                    'extracted_date' => $date,
                    'filename' => $originalFilename
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error importing Shopee SKU Excel: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
            return response()->json([
                'status' => 'error',
                'message' => 'Error importing Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract date from SKU Excel filename
     * Format: export_report.parentskudetail.YYYYMMDD_YYYYMMDD.xlsx
     */
    private function extractDateFromSkuFilename($filename)
    {
        // Default to today if no date found
        $defaultDate = Carbon::now()->format('Y-m-d');
        
        // Try to match the pattern in format export_report.parentskudetail.YYYYMMDD_YYYYMMDD.xlsx
        if (preg_match('/parentskudetail\.(\d{8})_/', $filename, $matches)) {
            $dateString = $matches[1]; // This will be YYYYMMDD
            try {
                return Carbon::createFromFormat('Ymd', $dateString)->format('Y-m-d');
            } catch (\Exception $e) {
                Log::warning("Failed to parse date from SKU filename: {$filename}. Using default date instead.");
                return $defaultDate;
            }
        }
        
        Log::warning("No date pattern found in SKU filename: {$filename}. Using default date instead.");
        return $defaultDate;
    }

    /**
     * Process Shopee SKU Excel data
     */
    private function processShopeeSkuExcel($filePath, $fileDate)
    {
        $data = [];
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        $worksheet = $spreadsheet->getActiveSheet();
        
        // Get the highest row
        $highestRow = $worksheet->getHighestRow();
        
        // Log the first few rows for debugging
        $headerRow = $worksheet->rangeToArray('A1:' . $worksheet->getHighestColumn() . '1', null, true, false)[0];
        Log::info("Excel Headers:", $headerRow);
        
        // Process each row
        for ($row = 2; $row <= $highestRow; $row++) {
            // Get the row data
            $rowData = $worksheet->rangeToArray('A' . $row . ':' . $worksheet->getHighestColumn() . $row, null, true, false)[0];
            
            // Skip rows where column 11 is "-"
            if (isset($rowData[11]) && $rowData[11] === '-') {
                continue;
            }
            
            // Extract required fields
            $kodeProduct = isset($rowData[0]) ? $rowData[0] : '';
            
            if (empty($kodeProduct)) {
                continue;
            }
            
            $skuInduk = isset($rowData[7]) ? $rowData[7] : '';
            
            $data[] = [
                'date' => $fileDate,
                'kode_produk' => $kodeProduct,
                'sku_induk' => $skuInduk,
                'pengunjung_produk_kunjungan' => $this->parseExcelNumeric($rowData[8] ?? 0),
                'halaman_produk_dilihat' => $this->parseExcelNumeric($rowData[9] ?? 0),
                'pengunjung_melihat_tanpa_membeli' => $this->parseExcelNumeric($rowData[10] ?? 0),
                'klik_pencarian' => $this->parseExcelNumeric($rowData[12] ?? 0),
                'suka' => $this->parseExcelNumeric($rowData[13] ?? 0),
                'pengunjung_produk_menambahkan_produk_ke_keranjang' => $this->parseExcelNumeric($rowData[14] ?? 0),
                'dimasukan_ke_keranjang_produk' => $this->parseExcelNumeric($rowData[15] ?? 0),
                'total_pembeli_pesanan_dibuat' => $this->parseExcelNumeric($rowData[17] ?? 0),
                'produk_pesanan_dibuat' => $this->parseExcelNumeric($rowData[18] ?? 0),
                'produk_pesanan_siap_dikirim' => $this->parseExcelNumeric($rowData[22] ?? 0),
                'total_pembeli_pesanan_siap_dikirim' => $this->parseExcelNumeric($rowData[21] ?? 0),
                'total_penjualan_pesanan_dibuat_idr' => $this->parseExcelNumeric($rowData[19] ?? 0),
                'penjualan_pesanan_siap_dikirim_idr' => $this->parseExcelNumeric($rowData[23] ?? 0),
            ];
        }
        
        return $data;
    }

    /**
     * Parse numeric value from Excel
     */
    private function parseExcelNumeric($value)
    {
        if (is_numeric($value)) {
            return (int) $value;
        }
        
        // Handle formatted numbers (e.g., "1,234")
        if (is_string($value)) {
            // Remove any non-numeric characters except decimal point
            $value = preg_replace('/[^0-9.]/', '', $value);
            return $value === '' ? 0 : (int) $value;
        }
        
        return 0;
    }

    /**
     * Save Shopee SKU data to database
     */
    private function saveShopeeSkuToDatabase($data)
    {
        $inserted = 0;
        $updated = 0;
        
        foreach ($data as $row) {
            try {
                // Check if a record already exists with the same date and kode_produk
                $existingRecord = AdsShopee::where('date', $row['date'])
                    ->where('kode_produk', $row['kode_produk'])
                    ->first();
                
                if ($existingRecord) {
                    // Update existing record with the SKU details
                    $existingRecord->update([
                        'sku_induk' => $row['sku_induk'],
                        'pengunjung_produk_kunjungan' => $row['pengunjung_produk_kunjungan'],
                        'halaman_produk_dilihat' => $row['halaman_produk_dilihat'],
                        'pengunjung_melihat_tanpa_membeli' => $row['pengunjung_melihat_tanpa_membeli'],
                        'klik_pencarian' => $row['klik_pencarian'],
                        'suka' => $row['suka'],
                        'pengunjung_produk_menambahkan_produk_ke_keranjang' => $row['pengunjung_produk_menambahkan_produk_ke_keranjang'],
                        'dimasukan_ke_keranjang_produk' => $row['dimasukan_ke_keranjang_produk'],
                        'total_pembeli_pesanan_dibuat' => $row['total_pembeli_pesanan_dibuat'],
                        'produk_pesanan_dibuat' => $row['produk_pesanan_dibuat'],
                        'produk_pesanan_siap_dikirim' => $row['produk_pesanan_siap_dikirim'],
                        'total_pembeli_pesanan_siap_dikirim' => $row['total_pembeli_pesanan_siap_dikirim'],
                        'total_penjualan_pesanan_dibuat_idr' => $row['total_penjualan_pesanan_dibuat_idr'],
                        'penjualan_pesanan_siap_dikirim_idr' => $row['penjualan_pesanan_siap_dikirim_idr'],
                    ]);
                    $updated++;
                } else {
                    // Create a new record with defaults for ad-specific fields
                    AdsShopee::create([
                        'date' => $row['date'],
                        'kode_produk' => $row['kode_produk'],
                        'sku_induk' => $row['sku_induk'],
                        'urutan' => 0,
                        'nama_iklan' => 'SKU Detail Import',
                        'status' => 'Imported',
                        'tampilan_iklan' => '',
                        'mode_bidding' => '',
                        'penempatan_iklan' => '',
                        'tanggal_mulai' => null,
                        'tanggal_selesai' => '',
                        'dilihat' => 0,
                        'jumlah_klik' => 0,
                        'konversi' => 0,
                        'produk_terjual' => 0,
                        'omzet_penjualan' => 0,
                        'biaya' => 0,
                        'efektivitas_iklan' => 0,
                        'pengunjung_produk_kunjungan' => $row['pengunjung_produk_kunjungan'],
                        'halaman_produk_dilihat' => $row['halaman_produk_dilihat'],
                        'pengunjung_melihat_tanpa_membeli' => $row['pengunjung_melihat_tanpa_membeli'],
                        'klik_pencarian' => $row['klik_pencarian'],
                        'suka' => $row['suka'],
                        'pengunjung_produk_menambahkan_produk_ke_keranjang' => $row['pengunjung_produk_menambahkan_produk_ke_keranjang'],
                        'dimasukan_ke_keranjang_produk' => $row['dimasukan_ke_keranjang_produk'],
                        'total_pembeli_pesanan_dibuat' => $row['total_pembeli_pesanan_dibuat'],
                        'produk_pesanan_dibuat' => $row['produk_pesanan_dibuat'],
                        'produk_pesanan_siap_dikirim' => $row['produk_pesanan_siap_dikirim'],
                        'total_pembeli_pesanan_siap_dikirim' => $row['total_pembeli_pesanan_siap_dikirim'],
                        'total_penjualan_pesanan_dibuat_idr' => $row['total_penjualan_pesanan_dibuat_idr'],
                        'penjualan_pesanan_siap_dikirim_idr' => $row['penjualan_pesanan_siap_dikirim_idr'],
                    ]);
                    $inserted++;
                }
            } catch (\Exception $e) {
                Log::error("Failed to save SKU record: " . $e->getMessage(), ['data' => $row]);
            }
        }
        
        return [
            'inserted' => $inserted,
            'updated' => $updated
        ];
    }
    public function get_ads_monitoring(Request $request)
{
    $query = AdsMonitoring::query();

    // Apply date filters
    if ($request->has('date_start') && $request->has('date_end')) {
        $query->whereBetween('date', [$request->date_start, $request->date_end]);
    }

    // Apply channel filter
    if ($request->has('channel') && $request->channel != '') {
        $query->where('channel', $request->channel);
    }

    return DataTables::of($query)
        ->addColumn('gmv_variance', function ($row) {
            if ($row->gmv_target && $row->gmv_actual) {
                $variance = (($row->gmv_actual - $row->gmv_target) / $row->gmv_target) * 100;
                $class = $variance >= 0 ? 'text-success' : 'text-danger';
                $icon = $variance >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                return '<span class="' . $class . '"><i class="fas ' . $icon . '"></i> ' . number_format($variance, 2) . '%</span>';
            }
            return '-';
        })
        ->addColumn('spent_variance', function ($row) {
            if ($row->spent_target && $row->spent_actual) {
                $variance = (($row->spent_actual - $row->spent_target) / $row->spent_target) * 100;
                $class = $variance >= 0 ? 'text-danger' : 'text-success';
                $icon = $variance >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                return '<span class="' . $class . '"><i class="fas ' . $icon . '"></i> ' . number_format($variance, 2) . '%</span>';
            }
            return '-';
        })
        ->addColumn('roas_variance', function ($row) {
            if ($row->roas_target && $row->roas_actual) {
                $variance = (($row->roas_actual - $row->roas_target) / $row->roas_target) * 100;
                $class = $variance >= 0 ? 'text-success' : 'text-danger';
                $icon = $variance >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                return '<span class="' . $class . '"><i class="fas ' . $icon . '"></i> ' . number_format($variance, 2) . '%</span>';
            }
            return '-';
        })
        ->addColumn('cpa_variance', function ($row) {
            if ($row->cpa_target && $row->cpa_actual) {
                $variance = (($row->cpa_actual - $row->cpa_target) / $row->cpa_target) * 100;
                $class = $variance >= 0 ? 'text-danger' : 'text-success';
                $icon = $variance >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
                return '<span class="' . $class . '"><i class="fas ' . $icon . '"></i> ' . number_format($variance, 2) . '%</span>';
            }
            return '-';
        })
        ->addColumn('daily_score', function ($row) {
            return $this->calculateDailyScore($row);
        })
        ->addColumn('performance_status', function ($row) {
            $totalScore = $this->calculateTotalScore($row);
            $maxScore = 102; // 20 + 25 + 25 + 15 + 15 + 2 (bonus points)
            
            $percentage = ($totalScore / $maxScore) * 100;
            
            if ($percentage >= 90) {
                return '<span class="badge badge-success">Excellent (' . $totalScore . '/' . $maxScore . ')</span>';
            } elseif ($percentage >= 80) {
                return '<span class="badge badge-primary">Very Good (' . $totalScore . '/' . $maxScore . ')</span>';
            } elseif ($percentage >= 70) {
                return '<span class="badge badge-info">Good (' . $totalScore . '/' . $maxScore . ')</span>';
            } elseif ($percentage >= 60) {
                return '<span class="badge badge-warning">Average (' . $totalScore . '/' . $maxScore . ')</span>';
            } else {
                return '<span class="badge badge-danger">Poor (' . $totalScore . '/' . $maxScore . ')</span>';
            }
        })
        ->editColumn('date', function ($row) {
            return Carbon::parse($row->date)->format('d/m/Y');
        })
        ->editColumn('gmv_target', function ($row) {
            return $row->gmv_target ? 'Rp ' . number_format($row->gmv_target, 0, ',', '.') : '-';
        })
        ->editColumn('gmv_actual', function ($row) {
            return $row->gmv_actual ? 'Rp ' . number_format($row->gmv_actual, 0, ',', '.') : '-';
        })
        ->editColumn('spent_target', function ($row) {
            return $row->spent_target ? 'Rp ' . number_format($row->spent_target, 0, ',', '.') : '-';
        })
        ->editColumn('spent_actual', function ($row) {
            return $row->spent_actual ? 'Rp ' . number_format($row->spent_actual, 0, ',', '.') : '-';
        })
        ->editColumn('roas_target', function ($row) {
            return $row->roas_target ? number_format($row->roas_target, 2) : '-';
        })
        ->editColumn('roas_actual', function ($row) {
            return $row->roas_actual ? number_format($row->roas_actual, 2) : '-';
        })
        ->editColumn('cpa_target', function ($row) {
            return $row->cpa_target ? 'Rp ' . number_format($row->cpa_target, 0, ',', '.') : '-';
        })
        ->editColumn('cpa_actual', function ($row) {
            return $row->cpa_actual ? 'Rp ' . number_format($row->cpa_actual, 0, ',', '.') : '-';
        })
        ->editColumn('aov_to_cpa_target', function ($row) {
            return $row->aov_to_cpa_target ? number_format($row->aov_to_cpa_target, 2) : '-';
        })
        ->editColumn('aov_to_cpa_actual', function ($row) {
            return $row->aov_to_cpa_actual ? number_format($row->aov_to_cpa_actual, 2) : '-';
        })
        ->rawColumns(['gmv_variance', 'spent_variance', 'roas_variance', 'cpa_variance', 'performance_status', 'daily_score'])
        ->make(true);
}

/**
 * Calculate daily score breakdown for each KPI
 */
private function calculateDailyScore($row)
{
    $scores = [];
    $details = [];
    
    // GMV Score (Bobot: 20, Max: 22)
    if ($row->gmv_target && $row->gmv_actual) {
        $achievement = ($row->gmv_actual / $row->gmv_target) * 100;
        if ($achievement >= 100) {
            $gmvScore = min(22, 20 + 2); // Base score + bonus
            $details[] = '<span class="text-success">GMV: 22/22 (' . number_format($achievement, 1) . '%)</span>';
        } else {
            $gmvScore = max(0, round(($achievement / 100) * 20));
            $details[] = '<span class="text-warning">GMV: ' . $gmvScore . '/22 (' . number_format($achievement, 1) . '%)</span>';
        }
        $scores[] = $gmvScore;
    } else {
        $details[] = '<span class="text-muted">GMV: N/A</span>';
    }
    
    // Spent Score (Bobot: 25, Max: 27) - Using your formula
    if ($row->spent_target && $row->spent_actual) {
        // Formula: (target-(actual-target))/target
        $spentPerformance = ($row->spent_target - ($row->spent_actual - $row->spent_target)) / $row->spent_target;
        $spentPercentage = $spentPerformance * 100;
        
        if ($spentPercentage >= 100) {
            $spentScore = min(27, 25 + 2); // Full score + bonus
            $details[] = '<span class="text-success">Spent: 27/27 (' . number_format($spentPercentage, 1) . '%)</span>';
        } else {
            $spentScore = max(0, round(($spentPerformance) * 25));
            $details[] = '<span class="text-warning">Spent: ' . $spentScore . '/27 (' . number_format($spentPercentage, 1) . '%)</span>';
        }
        $scores[] = $spentScore;
    } else {
        $details[] = '<span class="text-muted">Spent: N/A</span>';
    }
    
    // ROAS Score (Bobot: 25, Max: 27)
    if ($row->roas_target && $row->roas_actual) {
        $roasAchievement = ($row->roas_actual / $row->roas_target) * 100;
        if ($roasAchievement >= 100) {
            $roasScore = min(27, 25 + 2); // Base score + bonus
            $details[] = '<span class="text-success">ROAS: 27/27 (' . number_format($roasAchievement, 1) . '%)</span>';
        } else {
            $roasScore = max(0, round(($roasAchievement / 100) * 25));
            $details[] = '<span class="text-warning">ROAS: ' . $roasScore . '/27 (' . number_format($roasAchievement, 1) . '%)</span>';
        }
        $scores[] = $roasScore;
    } else {
        $details[] = '<span class="text-muted">ROAS: N/A</span>';
    }
    
    // CPA Score (Bobot: 15, Max: 17) - Lower is better
    if ($row->cpa_target && $row->cpa_actual) {
        $cpaRatio = ($row->cpa_actual / $row->cpa_target) * 100;
        if ($cpaRatio <= 100) {
            $cpaScore = min(17, 15 + 2); // Bonus for lower CPA
            $details[] = '<span class="text-success">CPA: 17/17 (' . number_format($cpaRatio, 1) . '%)</span>';
        } else {
            // Penalty for higher CPA
            $penalty = ($cpaRatio - 100) / 100;
            $cpaScore = max(0, round(15 - ($penalty * 15)));
            $details[] = '<span class="text-danger">CPA: ' . $cpaScore . '/17 (' . number_format($cpaRatio, 1) . '%)</span>';
        }
        $scores[] = $cpaScore;
    } else {
        $details[] = '<span class="text-muted">CPA: N/A</span>';
    }
    
    // AOV to CPA Score (Bobot: 15, Max: 17)
    if ($row->aov_to_cpa_target && $row->aov_to_cpa_actual) {
        $aovCpaAchievement = ($row->aov_to_cpa_actual / $row->aov_to_cpa_target) * 100;
        if ($aovCpaAchievement >= 100) {
            $aovCpaScore = min(17, 15 + 2); // Base score + bonus
            $details[] = '<span class="text-success">AOV/CPA: 17/17 (' . number_format($aovCpaAchievement, 1) . '%)</span>';
        } else {
            $aovCpaScore = max(0, round(($aovCpaAchievement / 100) * 15));
            $details[] = '<span class="text-warning">AOV/CPA: ' . $aovCpaScore . '/17 (' . number_format($aovCpaAchievement, 1) . '%)</span>';
        }
        $scores[] = $aovCpaScore;
    } else {
        $details[] = '<span class="text-muted">AOV/CPA: N/A</span>';
    }
    
    $totalScore = array_sum($scores);
    $maxPossible = 110; // 22 + 27 + 27 + 17 + 17
    
    $html = '<div class="score-breakdown">';
    $html .= '<div class="total-score mb-2"><strong>Total Score: ' . $totalScore . '/' . $maxPossible . '</strong></div>';
    $html .= '<div class="score-details" style="font-size: 11px;">';
    $html .= implode('<br>', $details);
    $html .= '</div>';
    $html .= '</div>';
    
    return $html;
}

/**
 * Calculate total score for performance status
 */
private function calculateTotalScore($row)
{
    $totalScore = 0;
    
    // GMV Score (Max: 22)
    if ($row->gmv_target && $row->gmv_actual) {
        $achievement = ($row->gmv_actual / $row->gmv_target) * 100;
        if ($achievement >= 100) {
            $totalScore += min(22, 20 + 2);
        } else {
            $totalScore += max(0, round(($achievement / 100) * 20));
        }
    }
    
    // Spent Score (Max: 27) - Using your formula
    if ($row->spent_target && $row->spent_actual) {
        // Formula: (target-(actual-target))/target
        $spentPerformance = ($row->spent_target - ($row->spent_actual - $row->spent_target)) / $row->spent_target;
        
        if ($spentPerformance >= 1.0) {
            $totalScore += min(27, 25 + 2);
        } else {
            $totalScore += max(0, round($spentPerformance * 25));
        }
    }
    
    // ROAS Score (Max: 27)
    if ($row->roas_target && $row->roas_actual) {
        $roasAchievement = ($row->roas_actual / $row->roas_target) * 100;
        if ($roasAchievement >= 100) {
            $totalScore += min(27, 25 + 2);
        } else {
            $totalScore += max(0, round(($roasAchievement / 100) * 25));
        }
    }
    
    // CPA Score (Max: 17)
    if ($row->cpa_target && $row->cpa_actual) {
        $cpaRatio = ($row->cpa_actual / $row->cpa_target) * 100;
        if ($cpaRatio <= 100) {
            $totalScore += min(17, 15 + 2);
        } else {
            $penalty = ($cpaRatio - 100) / 100;
            $totalScore += max(0, round(15 - ($penalty * 15)));
        }
    }
    
    // AOV to CPA Score (Max: 17)
    if ($row->aov_to_cpa_target && $row->aov_to_cpa_actual) {
        $aovCpaAchievement = ($row->aov_to_cpa_actual / $row->aov_to_cpa_target) * 100;
        if ($aovCpaAchievement >= 100) {
            $totalScore += min(17, 15 + 2);
        } else {
            $totalScore += max(0, round(($aovCpaAchievement / 100) * 15));
        }
    }
    
    return $totalScore;
}

    /**
     * Get chart data for ads monitoring
     */
    public function get_ads_monitoring_chart_data(Request $request)
    {
        try {
            $query = AdsMonitoring::query();

            // Apply date filters
            if ($request->has('date_start') && $request->has('date_end')) {
                $query->whereBetween('date', [$request->date_start, $request->date_end]);
            } else {
                // Default to last 30 days
                $query->where('date', '>=', Carbon::now()->subDays(30));
            }

            // Apply channel filter
            if ($request->has('channel') && $request->channel != '') {
                $query->where('channel', $request->channel);
            }

            $data = $query->orderBy('date')->get();

            // Group by date and channel for chart
            $chartData = [
                'dates' => [],
                'channels' => [],
                'datasets' => [
                    'gmv_target' => [],
                    'gmv_actual' => [],
                    'spent_target' => [],
                    'spent_actual' => [],
                    'roas_target' => [],
                    'roas_actual' => [],
                    'cpa_target' => [],
                    'cpa_actual' => []
                ]
            ];

            // Get unique dates and channels
            $dates = $data->pluck('date')->unique()->sort()->values();
            $channels = $data->pluck('channel')->unique()->values();

            $chartData['dates'] = $dates->map(function($date) {
                return Carbon::parse($date)->format('Y-m-d');
            })->toArray();

            $chartData['channels'] = $channels->toArray();

            // Process data for each channel
            foreach ($channels as $channel) {
                $channelData = $data->where('channel', $channel);
                
                foreach ($dates as $date) {
                    $dayData = $channelData->where('date', $date)->first();
                    
                    $chartData['datasets']['gmv_target'][$channel][] = $dayData ? (float)$dayData->gmv_target : null;
                    $chartData['datasets']['gmv_actual'][$channel][] = $dayData ? (float)$dayData->gmv_actual : null;
                    $chartData['datasets']['spent_target'][$channel][] = $dayData ? (float)$dayData->spent_target : null;
                    $chartData['datasets']['spent_actual'][$channel][] = $dayData ? (float)$dayData->spent_actual : null;
                    $chartData['datasets']['roas_target'][$channel][] = $dayData ? (float)$dayData->roas_target : null;
                    $chartData['datasets']['roas_actual'][$channel][] = $dayData ? (float)$dayData->roas_actual : null;
                    $chartData['datasets']['cpa_target'][$channel][] = $dayData ? (float)$dayData->cpa_target : null;
                    $chartData['datasets']['cpa_actual'][$channel][] = $dayData ? (float)$dayData->cpa_actual : null;
                }
            }

            return response()->json([
                'status' => 'success',
                'data' => $chartData
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export ads monitoring report
     */
    public function export_ads_monitoring(Request $request)
    {
        try {
            $query = AdsMonitoring::query();

            // Apply filters
            if ($request->has('date_start') && $request->has('date_end')) {
                $query->whereBetween('date', [$request->date_start, $request->date_end]);
            }

            if ($request->has('channel') && $request->channel != '') {
                $query->where('channel', $request->channel);
            }

            $data = $query->orderBy('date', 'desc')->orderBy('channel')->get();

            $filename = 'ads_monitoring_report_' . date('Y-m-d_H-i-s') . '.csv';
            
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function() use ($data) {
                $file = fopen('php://output', 'w');
                
                // CSV Headers
                fputcsv($file, [
                    'Date',
                    'Channel',
                    'GMV Target',
                    'GMV Actual',
                    'GMV Variance (%)',
                    'Spent Target',
                    'Spent Actual',
                    'Spent Variance (%)',
                    'ROAS Target',
                    'ROAS Actual',
                    'ROAS Variance (%)',
                    'CPA Target',
                    'CPA Actual',
                    'CPA Variance (%)',
                    'AOV/CPA Target',
                    'AOV/CPA Actual'
                ]);

                foreach ($data as $row) {
                    // Calculate variances
                    $gmvVariance = ($row->gmv_target && $row->gmv_actual) 
                        ? (($row->gmv_actual - $row->gmv_target) / $row->gmv_target) * 100 
                        : null;
                        
                    $spentVariance = ($row->spent_target && $row->spent_actual) 
                        ? (($row->spent_actual - $row->spent_target) / $row->spent_target) * 100 
                        : null;
                        
                    $roasVariance = ($row->roas_target && $row->roas_actual) 
                        ? (($row->roas_actual - $row->roas_target) / $row->roas_target) * 100 
                        : null;
                        
                    $cpaVariance = ($row->cpa_target && $row->cpa_actual) 
                        ? (($row->cpa_actual - $row->cpa_target) / $row->cpa_target) * 100 
                        : null;

                    fputcsv($file, [
                        Carbon::parse($row->date)->format('d/m/Y'),
                        $row->channel,
                        $row->gmv_target,
                        $row->gmv_actual,
                        $gmvVariance ? number_format($gmvVariance, 2) : '',
                        $row->spent_target,
                        $row->spent_actual,
                        $spentVariance ? number_format($spentVariance, 2) : '',
                        $row->roas_target,
                        $row->roas_actual,
                        $roasVariance ? number_format($roasVariance, 2) : '',
                        $row->cpa_target,
                        $row->cpa_actual,
                        $cpaVariance ? number_format($cpaVariance, 2) : '',
                        $row->aov_to_cpa_target,
                        $row->aov_to_cpa_actual
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to export report: ' . $e->getMessage()
            ], 500);
        }
    }
    public function refresh_tiktok_ads_monitoring(Request $request)
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m');
            $startDate = $currentMonth . '-01';
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

            $affectedRows = DB::statement("
                UPDATE ads_monitoring am
                JOIN (
                    SELECT 
                        date,
                        CAST(COALESCE(SUM(amount_spent), 0) AS DECIMAL(10,2)) as total_spent,
                        CAST(COALESCE(SUM(purchases_conversion_value_shared_items), 0) AS DECIMAL(15,2)) as total_gmv,
                        CAST(AVG(CASE WHEN cost_per_purchase IS NOT NULL THEN cost_per_purchase END) AS DECIMAL(10,2)) as avg_cpa
                    FROM ads_tiktok 
                    WHERE date >= ? 
                    AND date <= ?
                    AND amount_spent IS NOT NULL
                    AND purchases_conversion_value_shared_items IS NOT NULL
                    GROUP BY date
                ) at_grouped ON am.date = at_grouped.date
                SET 
                    am.spent_actual = at_grouped.total_spent,
                    am.gmv_actual = at_grouped.total_gmv,
                    am.cpa_actual = at_grouped.avg_cpa,
                    am.roas_actual = CASE 
                        WHEN at_grouped.total_spent > 0 THEN 
                            ROUND(at_grouped.total_gmv / at_grouped.total_spent, 4)
                        ELSE NULL 
                    END,
                    am.updated_at = NOW()
                WHERE am.channel = 'tiktok_ads'
                AND am.date >= ?
                AND am.date <= ?
            ", [$startDate, $endDate, $startDate, $endDate]);

            return response()->json([
                'status' => 'success',
                'message' => 'TikTok ads data refreshed successfully',
                'period' => $currentMonth,
                'affected_rows' => $affectedRows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh TikTok ads data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh Shopee ads actual data
     */
    public function refresh_shopee_ads_monitoring(Request $request)
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m');
            $startDate = $currentMonth . '-01';
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

            $affectedRows = DB::statement("
                UPDATE ads_monitoring am
                JOIN (
                    SELECT 
                        date,
                        SUM(biaya) as total_spent,
                        SUM(omzet_penjualan) as total_gmv,
                        AVG(CASE WHEN produk_terjual > 0 THEN biaya / produk_terjual ELSE NULL END) as avg_cpa
                    FROM ads_shopee 
                    WHERE date >= ? 
                    AND date <= ?
                    GROUP BY date
                ) as_grouped ON am.date = as_grouped.date
                SET 
                    am.spent_actual = as_grouped.total_spent,
                    am.gmv_actual = as_grouped.total_gmv,
                    am.cpa_actual = as_grouped.avg_cpa,
                    am.roas_actual = CASE 
                        WHEN as_grouped.total_spent > 0 THEN 
                            ROUND(as_grouped.total_gmv / as_grouped.total_spent, 4)
                        ELSE NULL 
                    END,
                    am.updated_at = NOW()
                WHERE am.channel = 'shopee_ads'
                AND am.date >= ?
                AND am.date <= ?
            ", [$startDate, $endDate, $startDate, $endDate]);

            return response()->json([
                'status' => 'success',
                'message' => 'Shopee ads data refreshed successfully',
                'period' => $currentMonth,
                'affected_rows' => $affectedRows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh Shopee ads data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh Meta ads actual data
     */
    public function refresh_meta_ads_monitoring(Request $request)
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m');
            $startDate = $currentMonth . '-01';
            $endDate = Carbon::now()->endOfMonth()->format('Y-m-d');

            $affectedRows = DB::statement("
                UPDATE ads_monitoring am
                JOIN (
                    SELECT 
                        date,
                        SUM(amount_spent) as total_spent,
                        SUM(purchases_conversion_value_shared_items) as total_gmv,
                        SUM(purchases_shared_items) as total_purchases,
                        AVG(CASE 
                            WHEN purchases_shared_items > 0 THEN amount_spent / purchases_shared_items 
                            ELSE NULL 
                        END) as avg_cpa
                    FROM ads_meta 
                    WHERE date >= ? 
                    AND date <= ?
                    GROUP BY date
                ) am_grouped ON am.date = am_grouped.date
                SET 
                    am.spent_actual = am_grouped.total_spent,
                    am.gmv_actual = am_grouped.total_gmv,
                    am.cpa_actual = am_grouped.avg_cpa,
                    am.roas_actual = CASE 
                        WHEN am_grouped.total_spent > 0 THEN 
                            ROUND(am_grouped.total_gmv / am_grouped.total_spent, 4)
                        ELSE NULL 
                    END,
                    am.updated_at = NOW()
                WHERE am.channel = 'meta_ads'
                AND am.date >= ?
                AND am.date <= ?
            ", [$startDate, $endDate, $startDate, $endDate]);

            return response()->json([
                'status' => 'success',
                'message' => 'Meta ads data refreshed successfully',
                'period' => $currentMonth,
                'affected_rows' => $affectedRows
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh Meta ads data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Refresh all ads monitoring data (TikTok, Shopee, Meta)
     */
    public function refresh_all_ads_monitoring(Request $request)
    {
        try {
            $results = [];
            $currentMonth = Carbon::now()->format('Y-m');

            // Refresh TikTok
            $tiktokResponse = $this->refresh_tiktok_ads_monitoring($request);
            $tiktokData = json_decode($tiktokResponse->getContent(), true);
            $results['tiktok'] = $tiktokData;

            // Refresh Shopee
            $shopeeResponse = $this->refresh_shopee_ads_monitoring($request);
            $shopeeData = json_decode($shopeeResponse->getContent(), true);
            $results['shopee'] = $shopeeData;

            // Refresh Meta
            $metaResponse = $this->refresh_meta_ads_monitoring($request);
            $metaData = json_decode($metaResponse->getContent(), true);
            $results['meta'] = $metaData;

            // Check if any failed
            $hasErrors = collect($results)->contains(function($result) {
                return $result['status'] === 'error';
            });

            if ($hasErrors) {
                return response()->json([
                    'status' => 'partial_success',
                    'message' => 'Some ads data refresh operations had errors',
                    'results' => $results,
                    'period' => $currentMonth
                ], 207); // Multi-Status
            }

            return response()->json([
                'status' => 'success',
                'message' => 'All ads monitoring data refreshed successfully',
                'results' => $results,
                'period' => $currentMonth,
                'total_affected_rows' => array_sum(array_column($results, 'affected_rows'))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to refresh all ads monitoring data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get refresh status and last update info
     */
    public function get_ads_monitoring_refresh_status(Request $request)
    {
        try {
            $currentMonth = Carbon::now()->format('Y-m');
            
            $status = DB::select("
                SELECT 
                    channel,
                    COUNT(*) as total_records,
                    COUNT(CASE WHEN spent_actual IS NOT NULL THEN 1 END) as records_with_actual,
                    MAX(updated_at) as last_updated,
                    MIN(date) as period_start,
                    MAX(date) as period_end
                FROM ads_monitoring 
                WHERE DATE_FORMAT(date, '%Y-%m') = ?
                GROUP BY channel
                ORDER BY channel
            ", [$currentMonth]);

            return response()->json([
                'status' => 'success',
                'data' => $status,
                'period' => $currentMonth,
                'last_check' => Carbon::now()->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get refresh status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
 * Debug version of get_spent_vs_gmv with detailed logging
 */
public function get_spent_vs_gmv(Request $request)
{
    try {
        $tenantId = auth()->user()->tenant_id;
        
        // Debug: Log the inputs
        \Log::info('Spent vs GMV Debug', [
            'tenant_id' => $tenantId,
            'date_start' => $request->date_start,
            'date_end' => $request->date_end,
            'channel' => $request->channel,
            'all_request_data' => $request->all()
        ]);
        
        // Set default date range if not provided
        $dateStart = $request->date_start ?: '2025-06-01';
        $dateEnd = $request->date_end ?: '2025-06-30';
        
        // Debug: Log the dates being used
        \Log::info('Date range being used', [
            'dateStart' => $dateStart,
            'dateEnd' => $dateEnd
        ]);
        
        $sql = "
            SELECT 
                'Shopee and Meta' as channel_name,
                COALESCE(sales_data.date, spend_data.date) as date,
                COALESCE(sales_data.sales_amount, 0) as sales_amount,
                COALESCE(spend_data.spend_amount, 0) as spend_amount,
                CASE 
                    WHEN COALESCE(spend_data.spend_amount, 0) > 0 
                    THEN COALESCE(sales_data.sales_amount, 0) / spend_data.spend_amount 
                    ELSE 0 
                END as roas,
                CASE 
                    WHEN COALESCE(sales_data.sales_amount, 0) > 0 
                    THEN (COALESCE(spend_data.spend_amount, 0) / sales_data.sales_amount) * 100 
                    ELSE 0 
                END as spent_percentage
            FROM (
                SELECT 
                    o.date,
                    SUM(o.amount) as sales_amount
                FROM orders o
                JOIN sales_channels sc ON o.sales_channel_id = sc.id
                WHERE o.tenant_id = ?
                    AND o.date >= ? 
                    AND o.date <= ?
                    AND o.sales_channel_id = 1
                    AND o.status NOT IN (
                        'pending', 
                        'cancelled', 
                        'request_cancel', 
                        'request_return',
                        'Batal', 
                        'Canceled', 
                        'Pembatalan diajukan', 
                        'Dibatalkan Sistem'
                    )
                GROUP BY o.date
            ) sales_data
            LEFT JOIN (
                SELECT 
                    date,
                    SUM(amount) as spend_amount
                FROM (
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_social_media asmp
                    JOIN social_media sm ON asmp.social_media_id = sm.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= ? 
                        AND asmp.date <= ?
                        AND asmp.social_media_id = 1
                    
                    UNION ALL
                    
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_market_places asmp
                    JOIN sales_channels sc ON asmp.sales_channel_id = sc.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= ? 
                        AND asmp.date <= ?
                        AND asmp.sales_channel_id = 1
                ) combined_spend
                GROUP BY date
            ) spend_data ON sales_data.date = spend_data.date
            ORDER BY COALESCE(sales_data.date, spend_data.date) DESC
        ";

        $bindings = [
            $tenantId, $dateStart, $dateEnd,  // sales query
            $tenantId, $dateStart, $dateEnd,  // social media spend
            $tenantId, $dateStart, $dateEnd   // marketplace spend
        ];

        // Debug: Log the SQL and bindings
        \Log::info('SQL Query', [
            'sql' => $sql,
            'bindings' => $bindings
        ]);

        $data = DB::select($sql, $bindings);

        // Debug: Log the raw data
        \Log::info('Raw query result', [
            'count' => count($data),
            'data' => $data
        ]);

        // Filter by channel if specified
        if ($request->filled('channel') && $request->channel !== 'shopee_and_meta') {
            $data = collect($data)->where('channel_name', $request->channel)->values()->all();
            \Log::info('After channel filter', [
                'channel' => $request->channel,
                'count' => count($data)
            ]);
        }

        // If still no data, let's test individual queries
        if (empty($data)) {
            // Test sales data only
            $salesSql = "
                SELECT 
                    o.date,
                    SUM(o.amount) as sales_amount
                FROM orders o
                JOIN sales_channels sc ON o.sales_channel_id = sc.id
                WHERE o.tenant_id = ?
                    AND o.date >= ? 
                    AND o.date <= ?
                    AND o.sales_channel_id = 1
                    AND o.status NOT IN (
                        'pending', 'cancelled', 'request_cancel', 'request_return',
                        'Batal', 'Canceled', 'Pembatalan diajukan', 'Dibatalkan Sistem'
                    )
                GROUP BY o.date
                ORDER BY o.date DESC
            ";
            
            $salesData = DB::select($salesSql, [$tenantId, $dateStart, $dateEnd]);
            \Log::info('Sales data only', [
                'count' => count($salesData),
                'data' => $salesData
            ]);

            // Test spend data only
            $spendSql = "
                SELECT 
                    date,
                    SUM(amount) as spend_amount
                FROM (
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_social_media asmp
                    JOIN social_media sm ON asmp.social_media_id = sm.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= ? 
                        AND asmp.date <= ?
                        AND asmp.social_media_id = 1
                    
                    UNION ALL
                    
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_market_places asmp
                    JOIN sales_channels sc ON asmp.sales_channel_id = sc.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= ? 
                        AND asmp.date <= ?
                        AND asmp.sales_channel_id = 1
                ) combined_spend
                GROUP BY date
                ORDER BY date DESC
            ";
            
            $spendData = DB::select($spendSql, [$tenantId, $dateStart, $dateEnd, $tenantId, $dateStart, $dateEnd]);
            \Log::info('Spend data only', [
                'count' => count($spendData),
                'data' => $spendData
            ]);
        }

        $collection = collect($data);

        return DataTables::of($collection)
            ->editColumn('date', function($row) {
                return $row->date;
            })
            ->editColumn('sales_amount', function($row) {
                return number_format($row->sales_amount, 0);
            })
            ->editColumn('spend_amount', function($row) {
                return number_format($row->spend_amount, 0);
            })
            ->editColumn('roas', function($row) {
                return number_format($row->roas, 2);
            })
            ->editColumn('spent_percentage', function($row) {
                return number_format($row->spent_percentage, 2);
            })
            ->make(true);

    } catch (\Exception $e) {
        \Log::error('Spent vs GMV Error: ' . $e->getMessage(), [
            'stack_trace' => $e->getTraceAsString()
        ]);
        return response()->json([
            'error' => 'Failed to fetch data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Alternative method - Test with your exact working query
 */
public function test_spent_vs_gmv(Request $request)
{
    try {
        $tenantId = auth()->user()->tenant_id;
        
        // Use your exact working query structure
        $sql = "
            SELECT 
                'Shopee and Meta' as channel_name,
                COALESCE(sales_data.date, spend_data.date) as date,
                COALESCE(sales_data.sales_amount, 0) as sales_amount,
                COALESCE(spend_data.spend_amount, 0) as spend_amount
            FROM (
                SELECT 
                    o.date,
                    SUM(o.amount) as sales_amount
                FROM orders o
                JOIN sales_channels sc ON o.sales_channel_id = sc.id
                WHERE o.tenant_id = ?
                    AND o.date >= '2025-06-01' 
                    AND o.date < '2025-07-01'
                    AND o.sales_channel_id = 1
                    AND o.status NOT IN (
                        'pending', 
                        'cancelled', 
                        'request_cancel', 
                        'request_return',
                        'Batal', 
                        'Canceled', 
                        'Pembatalan diajukan', 
                        'Dibatalkan Sistem'
                    )
                GROUP BY o.date
            ) sales_data
            LEFT JOIN (
                SELECT 
                    date,
                    SUM(amount) as spend_amount
                FROM (
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_social_media asmp
                    JOIN social_media sm ON asmp.social_media_id = sm.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= '2025-06-01' 
                        AND asmp.date < '2025-07-01'
                        AND asmp.social_media_id = 1
                    
                    UNION ALL
                    
                    SELECT 
                        asmp.date,
                        asmp.amount
                    FROM ad_spent_market_places asmp
                    JOIN sales_channels sc ON asmp.sales_channel_id = sc.id
                    WHERE asmp.tenant_id = ? 
                        AND asmp.date >= '2025-06-01' 
                        AND asmp.date < '2025-07-01'
                        AND asmp.sales_channel_id = 1
                ) combined_spend
                GROUP BY date
            ) spend_data ON sales_data.date = spend_data.date
            ORDER BY date
        ";

        $data = DB::select($sql, [$tenantId, $tenantId, $tenantId]);
        
        // Calculate ROAS and percentage in PHP
        $processedData = collect($data)->map(function($row) {
            $roas = $row->spend_amount > 0 ? $row->sales_amount / $row->spend_amount : 0;
            $spentPercentage = $row->sales_amount > 0 ? ($row->spend_amount / $row->sales_amount) * 100 : 0;
            
            return [
                'channel_name' => $row->channel_name,
                'date' => $row->date,
                'sales_amount' => $row->sales_amount,
                'spend_amount' => $row->spend_amount,
                'roas' => $roas,
                'spent_percentage' => $spentPercentage
            ];
        });

        return DataTables::of($processedData)
            ->editColumn('sales_amount', function($row) {
                return number_format($row['sales_amount'], 0);
            })
            ->editColumn('spend_amount', function($row) {
                return number_format($row['spend_amount'], 0);
            })
            ->editColumn('roas', function($row) {
                return number_format($row['roas'], 2);
            })
            ->editColumn('spent_percentage', function($row) {
                return number_format($row['spent_percentage'], 2);
            })
            ->make(true);

    } catch (\Exception $e) {
        \Log::error('Test Spent vs GMV Error: ' . $e->getMessage());
        return response()->json([
            'error' => 'Failed to fetch test data: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * Simple test to check basic data
 */
public function simple_test(Request $request)
{
    try {
        $tenantId = auth()->user()->tenant_id;
        
        // Just get some basic order data
        $orders = DB::table('orders')
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', '2025-06-01')
            ->where('date', '<', '2025-07-01')
            ->where('sales_channel_id', 1)
            ->select('date', 'amount', 'status')
            ->limit(10)
            ->get();
            
        return response()->json([
            'tenant_id' => $tenantId,
            'orders_count' => $orders->count(),
            'orders_sample' => $orders
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ]);
    }
}
}
