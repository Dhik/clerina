<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\AdSpentMarketPlace\AdSpentMarketPlaceBLL;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLL;
use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Models\NetProfit;
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Sales\BLL\Visit\VisitBLLInterface;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\OperationalSpent;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Order\Models\Order;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Carbon\Carbon; 
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Facades\DB;
use App\Domain\Sales\Services\TelegramService;
use App\Domain\Sales\Services\GoogleSheetService;
use Illuminate\Support\Facades\Http;

class SalesController extends Controller
{
    protected $telegramService;
    protected $googleSheetService;

    public function __construct(
        protected AdSpentMarketPlaceBLL $adSpentMarketPlaceBLL,
        protected AdSpentSocialMediaBLL $adSpentSocialMediaBLL,
        protected SalesBLLInterface $salesBLL,
        protected SalesChannelBLLInterface $salesChannelBLL,
        protected SocialMediaBLLInterface $socialMediaBLL,
        protected VisitBLLInterface $visitBLL,
        TelegramService $telegramService,
        GoogleSheetService $googleSheetService
    ) {
        $this->telegramService = $telegramService;
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAnySales', Sales::class);

        $orderQuery = $this->salesBLL->getSalesDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($orderQuery)
            ->addColumn('visitFormatted', function ($row) {
                return '<a href="#" class="visitButtonDetail">'.
                        number_format($row->visit, 0, ',', '.').
                    '</a>';
            })
            ->addColumn('qtyFormatted', function ($row) {
                return number_format($row->qty, 0, ',', '.');
            })
            ->addColumn('totalFormatted', function ($row) {
                return 'Rp.'. number_format($row->ad_spent_social_media + $row->ad_spent_market_place, 0, ',', '.');
            })
            ->addColumn('adSpentSocialMediaFormatted', function ($row) {
                return 'Rp.'. number_format($row->ad_spent_social_media, 0, ',', '.');
            })
            ->addColumn('adSpentMarketPlaceFormatted', function ($row) {
                return 'Rp.'. number_format($row->ad_spent_market_place, 0, ',', '.');
            })
            ->addColumn('orderFormatted', function ($row) {
                return number_format($row->order, 0, ',', '.');
            })
            ->addColumn('closingRateFormatted', function ($row) {
                return $row->visit === 0 ? 0 : number_format(($row->order/$row->visit)*100, 2, ',', '.').'%';
            })
            ->addColumn('roasFormatted', function ($row) {
                return number_format($row->roas, 2, ',', '.');
            })
            ->addColumn('adSpentTotalFormatted', function ($row) {
                return '<a href="#" class="omsetButtonDetail">'.
                    number_format($row->turnover, 0, ',', '.').
                    '</a>';
            })
            ->addColumn('turnoverFormatted', function ($row) {
                return 'Rp.'. number_format($row->turnover, 0, ',', '.');
            })
            ->addColumn(
                'actions',
                '<a href="{{ URL::route( \'sales.show\', array( $id )) }}" class="btn btn-primary btn-sm" >
                            <i class="fas fa-eye"></i>
                        </a>'
            )
            ->rawColumns(['actions', 'visitFormatted', 'adSpentTotalFormatted'])
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();
        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.sales.index', compact('salesChannels', 'socialMedia'));
    }
    public function net_sales(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();
        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.sales.net_sales', compact('salesChannels', 'socialMedia'));
    }
    public function net_per_channel(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();
        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.sales.net_per_channel', compact('salesChannels', 'socialMedia'));
    }
    public function getNetProfit(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        
        $startDate = null;
        $endDate = null;
        
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }
        
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
            ->where('np.tenant_id', '=', $currentTenantId);
        
        // Apply date filtering
        if (!is_null($request->input('filterDates'))) {
            $baseQuery->where('np.date', '>=', $startDate)
                    ->where('np.date', '<=', $endDate);
        } else {
            $baseQuery->whereMonth('np.date', Carbon::now()->month)
                    ->whereYear('np.date', Carbon::now()->year);
        }
        
        $baseQuery->orderBy('np.date');
        $data = $baseQuery->get();
        
        return DataTables::of($data)
            ->addColumn('total_sales', function ($row) {
                return ($row->sales ?? 0);
            })
            ->addColumn('estimasi_cancel', function ($row) {
                return ($row->sales * 0.07?? 0);
            })
            ->addColumn('estimasi_retur', function ($row) {
                return ($row->sales * 0.02 ?? 0);
            })
            ->addColumn('mp_sales', function ($row) {
                return ($row->sales ?? 0);
            })
            ->addColumn('penjualan_bersih', function ($row) {
                $totalSales = ($row->sales ?? 0);
                return $totalSales * 0.743;
            })
            ->addColumn('romi', function ($row) {
                $totalMarketingSpend = $row->marketing + $row->spent_kol + ($row->affiliate ?? 0);
                
                if ($totalMarketingSpend == 0) {
                    return 0;
                }
                return $row->sales / $totalMarketingSpend;
            })
            ->addColumn('net_profit', function ($row) {
                return ($row->sales * 0.743) - 
                    ($row->marketing * 1.05) - 
                    $row->spent_kol - 
                    ($row->affiliate ?? 0) - 
                    $row->operasional - 
                    ($row->hpp * 0.96);
            })
            ->addColumn('estimasi_fee_admin', function ($row) {
                // 16.7% from sales
                return $row->sales * 0.167;
            })
	        ->addColumn('estimasi_cancelation', function ($row) {
                // 4% from sales
                return $row->sales * 0.04;
            })
            ->addColumn('estimasi_retur', function ($row) {
                // 1% from sales
                return $row->sales * 0.01;
            })
            ->addColumn('ppn', function ($row) {
                // 3% from sales
                return $row->sales * 0.03;
            })
            ->addColumn('fee_ads', function ($row) {
                // 2% from marketing
                return $row->marketing * 0.02;
            })
            ->addColumn('total_marketing_spend', function ($row) {
                return $row->marketing + $row->spent_kol + ($row->affiliate ?? 0);
            })
            ->editColumn('fee_packing', function ($row) {
                // Return fee_packing from database
                return $row->fee_packing ?? 0;
            })
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d');
            })
            ->editColumn('ad_spent_social_media', function ($row) {
                return $row->ad_spent_social_media ?? 0;
            })
            ->editColumn('ad_spent_market_place', function ($row) {
                return $row->ad_spent_market_place ?? 0;
            })
            ->editColumn('visit', function ($row) {
                return $row->visit ?? 0;
            })
            ->editColumn('qty', function ($row) {
                return $row->qty ?? 0;
            })
            ->editColumn('order', function ($row) {
                return $row->order ?? 0;
            })
            ->editColumn('closing_rate', function ($row) {
                return number_format($row->closing_rate ?? 0, 2) . '%';
            })
            ->editColumn('b2b_sales', function ($row) {
                return $row->b2b_sales ?? 0;
            })
            ->editColumn('crm_sales', function ($row) {
                return $row->crm_sales ?? 0;
            })
            ->editColumn('balance_amount', function ($row) {
                return $row->balance_amount ?? 0;
            })
            ->editColumn('gross_revenue', function ($row) {
                return $row->gross_revenue ?? 0;
            })
            ->editColumn('fee_admin', function ($row) {
                return $row->fee_admin ?? 0;
            })
            ->editColumn('roas', function ($row) {
                return number_format($row->roas ?? 0, 2);
            })
            ->make(true);
    }
    public function getNetProfitSummary(Request $request)
    {
        $query = NetProfit::query();

        $query->where('tenant_id', Auth::user()->current_tenant_id);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        } else {
            // Get the first day of the current month and today's date
            $startOfMonth = Carbon::now()->startOfMonth()->format('Y-m-d');
            $today = Carbon::now()->format('Y-m-d');
            
            $query->where('date', '>=', $startOfMonth)
                ->where('date', '<=', $today);
        }

        $data = $query->selectRaw('
            SUM((sales, 0)) as total_sales,
            SUM(hpp) * 0.96 as total_hpp,
            SUM(COALESCE(marketing, 0) + COALESCE(spent_kol, 0) + COALESCE(affiliate, 0) + COALESCE(operasional, 0)) as total_spent,
            SUM(marketing + COALESCE(spent_kol, 0) + COALESCE(affiliate, 0)) as total_marketing_spent,
            SUM((sales * 0.743) - (marketing * 1.05) - COALESCE(spent_kol, 0) - COALESCE(affiliate, 0) - operasional - (hpp * 0.96)) as total_net_profit,
            SUM(COALESCE(sales, 0) + COALESCE(b2b_sales, 0) + COALESCE(crm_sales, 0)) / NULLIF(SUM(marketing + COALESCE(spent_kol, 0) + COALESCE(affiliate, 0)), 0) as avg_romi,
            SUM((sales * 0.743) as total_net_sales,
            SUM((COALESCE(sales, 0) * 0.743) + COALESCE(b2b_sales, 0) + COALESCE(crm_sales, 0)) / NULLIF(SUM(marketing + COALESCE(spent_kol, 0) + COALESCE(affiliate, 0)), 0) as avg_net_romi
        ')
        ->first();

        return response()->json([
            'total_sales' => $data->total_sales ?? 0,
            'total_hpp' => $data->total_hpp ?? 0,
            'total_spent' => $data->total_spent ?? 0,
            'total_marketing_spent' => $data->total_marketing_spent ?? 0,
            'total_net_profit' => $data->total_net_profit ?? 0,
            'avg_romi' => $data->avg_romi ?? 0,
            'total_net_sales' => $data->total_net_sales ?? 0,
            'avg_net_romi' => $data->avg_net_romi ?? 0,
        ]);
    }
    public function getHPPChannelSummary(Request $request)
    {
        $query = DB::table('orders')
            ->leftJoin('products', 'orders.sku', '=', 'products.sku')
            ->where('orders.tenant_id', Auth::user()->current_tenant_id)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ]);

        // Apply date filter if provided
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
            $query->whereBetween('orders.date', [$startDate, $endDate]);
        } else {
            // Default to current month
            $query->whereMonth('orders.date', Carbon::now()->month)
                ->whereYear('orders.date', Carbon::now()->year);
        }

        // Apply sales channel filter if provided
        if ($request->filterChannel) {
            $query->where('orders.sales_channel_id', $request->filterChannel);
        }

        $data = $query->selectRaw('
            SUM(orders.amount) as total_sales,
            SUM(products.harga_satuan * orders.qty) as total_hpp,
            SUM(orders.qty) as total_qty,
            COUNT(DISTINCT orders.id_order) as order_count
        ')
        ->first();

        return response()->json([
            'total_sales' => $data->total_sales ?? 0,
            'total_hpp' => $data->total_hpp ?? 0,
            'total_qty' => $data->total_qty ?? 0,
            'order_count' => $data->order_count ?? 0
        ]);
    }
    public function getHppDetail(Request $request)
    {
        $date = $request->input('date');
        
        $query = DB::table('orders')
            ->select(
                'orders.sku',
                'products.product',
                DB::raw('SUM(orders.qty) as qty'),
                'products.harga_satuan',
                DB::raw('SUM(products.harga_satuan * orders.qty) as total_hpp')
            )
            ->leftJoin('products', 'orders.sku', '=', 'products.sku')
            ->where('orders.date', $date)
            ->where('orders.tenant_id', Auth::user()->current_tenant_id)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ]);
        
        // Apply sales channel filter if provided
        if ($request->filterChannel) {
            $query->where('orders.sales_channel_id', $request->filterChannel);
        }
        
        $results = $query->groupBy('orders.sku', 'products.product', 'products.harga_satuan')
            ->get();
        
        return response()->json(['data' => $results]);
    }
    public function getHppDetailTotal(Request $request)
    {
        $date = $request->input('date');
        
        $query = DB::table('orders')
            ->select(
                DB::raw('SUM(products.harga_satuan * orders.qty) as total_hpp')
            )
            ->leftJoin('products', 'orders.sku', '=', 'products.sku')
            ->where('orders.date', $date)
            ->where('orders.tenant_id', Auth::user()->current_tenant_id)
            ->whereNotIn('orders.status', [
                'pending', 
                'cancelled', 
                'request_cancel', 
                'request_return',
                'Batal', 
                'Canceled', 
                'Pembatalan diajukan', 
                'Dibatalkan Sistem'
            ]);
        
        // Apply sales channel filter if provided
        if ($request->filterChannel) {
            $query->where('orders.sales_channel_id', $request->filterChannel);
        }
        
        $result = $query->first();
        
        return response()->json([
            'total_hpp' => $result->total_hpp ?? 0
        ]);
    }
    public function getChartData(Request $request)
    {
        $query = NetProfit::query();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        $data = $query->orderBy('date')
            ->get()
            ->map(function ($row) {
                return [
                    'date' => Carbon::parse($row->date)->format('Y-m-d'),
                    'sales' => $row->sales,
                    'marketing' => $row->marketing,
                    'hpp' => $row->hpp,
                    'netProfit' => ($row->sales * 0.78) - 
                        ($row->marketing * 1.05) - 
                        $row->spent_kol - 
                        ($row->affiliate ?? 0) - 
                        $row->operasional - 
                        $row->hpp
                ];
            });

        return response()->json($data);
    }

    public function report(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);
        
        $socialMedia = SocialMedia::select('id', 'name')
            ->orderBy('name')
            ->get();
            
        $salesChannels = SalesChannel::select('id', 'name')
            ->orderBy('name')
            ->get();
        
        return view('admin.sales.report', compact('socialMedia', 'salesChannels'));
    }

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getSalesRecap(Request $request): JsonResponse
    {
        return response()->json($this->salesBLL->getSalesRecap($request, Auth::user()->current_tenant_id));
    }

    /**
     * Display a listing of the resource.
     */
    public function show(Sales $sales): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);

        $visits = $this->visitBLL->getVisitByDate($sales->date, Auth::user()->current_tenant_id);
        $adSpentMarketPlaces = $this->adSpentMarketPlaceBLL->getAdSpentMarketPlaceByDate($sales->date, Auth::user()->current_tenant_id);
        $adSpentSocialMedia = $this->adSpentSocialMediaBLL->getAdSpentSocialMediaByDate($sales->date, Auth::user()->current_tenant_id);

        return view('admin.sales.show', compact(
            'sales', 'visits', 'adSpentMarketPlaces', 'adSpentSocialMedia'
        ));
    }

    /**
     * Sync sales
     */
    public function syncSales(Sales $sales): RedirectResponse
    {
        $this->authorize('updateSales', Sales::class);

        $this->salesBLL->createSales($sales->date, Auth::user()->current_tenant_id);

        return redirect()->route('sales.show', $sales)->with([
            'alert' => 'success',
            'message' => trans('messages.success_update', ['model' => trans('labels.sales')]),
        ]);
    }

    public function getOmsetByDate($date): JsonResponse
    {
        $this->authorize('viewAnySales', Sales::class);

        $omsetData = Sales::whereDate('created_at', $date)
            ->groupBy('date')
            ->get();

        return response()->json($omsetData);
    }
    public function forAISalesCleora()
    {
        $yesterday = now()->subDay();
        $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

        $yesterdayData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('SUM(amount) as turnover')
            ->first(); 

        $orderData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('COUNT(id) as transactions, COUNT(DISTINCT customer_phone_number) as customers')
            ->first();

        $avgTurnoverPerTransaction = $orderData->transactions > 0 
            ? round($yesterdayData->turnover / $orderData->transactions, 2) 
            : 0;

        $avgTurnoverPerCustomer = $orderData->customers > 0 
            ? round($yesterdayData->turnover / $orderData->customers, 2) 
            : 0;

        // Format daily turnover
        $formattedTurnover = number_format($yesterdayData->turnover, 0, ',', '.');
        $formattedAvgPerTransaction = number_format($avgTurnoverPerTransaction, 0, ',', '.');
        $formattedAvgPerCustomer = number_format($avgTurnoverPerCustomer, 0, ',', '.');

        $startOfMonth = now()->startOfMonth();
        $thisMonthData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('SUM(amount) as total_turnover')
            ->first();

        $thisMonthOrderData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('COUNT(id) as total_transactions, COUNT(DISTINCT customer_phone_number) as total_customers')
            ->first();

        $formattedMonthTurnover = number_format($thisMonthData->total_turnover, 0, ',', '.');
        $formattedMonthTransactions = number_format($thisMonthOrderData->total_transactions, 0, ',', '.');
        $formattedMonthCustomers = number_format($thisMonthOrderData->total_customers, 0, ',', '.');

        $daysPassed = now()->day - 1;
        $remainingDays = now()->daysInMonth - $daysPassed;

        $avgDailyTurnover = $daysPassed > 0 ? $thisMonthData->total_turnover / $daysPassed : 0;
        $avgDailyTransactions = $daysPassed > 0 ? $thisMonthOrderData->total_transactions / $daysPassed : 0;
        $avgDailyCustomers = $daysPassed > 0 ? $thisMonthOrderData->total_customers / $daysPassed : 0;

        $projectedTurnover = $thisMonthData->total_turnover + ($avgDailyTurnover * $remainingDays);
        $projectedTransactions = $thisMonthOrderData->total_transactions + ($avgDailyTransactions * $remainingDays);
        $projectedCustomers = $thisMonthOrderData->total_customers + ($avgDailyCustomers * $remainingDays);

        // Format projections
        $formattedProjectedTurnover = number_format($projectedTurnover, 0, ',', '.');
        $formattedProjectedTransactions = number_format($projectedTransactions, 0, ',', '.');
        $formattedProjectedCustomers = number_format($projectedCustomers, 0, ',', '.');

        // Calculate turnover per sales channel
        $salesChannelData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        // Monthly data per sales channel for projection
        $thisMonthSalesChannelData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        // Map sales channel data to names and calculate projections
        $salesChannelNames = SalesChannel::pluck('name', 'id');
        $salesChannelTurnover = $salesChannelData->map(function ($item) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $formattedAmount = number_format($item->total_amount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedAmount}";
        })->implode("\n");

        $totalProjectedTurnover = $thisMonthSalesChannelData->reduce(function ($carry, $item) use ($daysPassed, $remainingDays) {
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            return $carry + $item->total_amount + ($dailyAverage * $remainingDays);
        }, 0);
        
        $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($salesChannelNames, $daysPassed, $remainingDays, $totalProjectedTurnover) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
            $percentage = $totalProjectedTurnover > 0 ? round(($projectedAmount / $totalProjectedTurnover) * 100, 2) : 0;
            $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedProjectedAmount} ({$percentage}%)";
        })->implode("\n");

        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->startOfMonth()->addDays(now()->day - 1);

        $lastMonthData = Sales::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->where('tenant_id', 1)
            ->selectRaw('SUM(turnover) as total_turnover')
            ->first();

        $growthMTDLM = $lastMonthData->total_turnover > 0
            ? round((($thisMonthData->total_turnover - $lastMonthData->total_turnover) / $lastMonthData->total_turnover) * 100, 2)
            : 0;
        
        $dayBeforeYesterday = now()->subDays(2);

        $dayBeforeYesterdayData = Sales::whereDate('date', $dayBeforeYesterday)
            ->where('tenant_id', 1)
            ->select('turnover')
            ->first();

        $growthYesterdayPast2Days = $dayBeforeYesterdayData && $dayBeforeYesterdayData->turnover > 0
            ? round((($yesterdayData->turnover - $dayBeforeYesterdayData->turnover) / $dayBeforeYesterdayData->turnover) * 100, 2)
            : 0;
        $response = [
            "report_date" => $yesterdayDateFormatted,
            "report_type" => "daily",
            "daily_metrics" => [
                "date" => $yesterdayDateFormatted,
                "transactions" => [
                    "total_revenue" => (int)$yesterdayData->turnover,
                    "total_transactions" => (int)$orderData->transactions,
                    "unique_customers" => (int)$orderData->customers,
                    "average_transaction_value" => (int)$avgTurnoverPerTransaction,
                    "daily_growth_rate" => $growthYesterdayPast2Days
                ],
            ]
        ];
        return response()->json($response);
    }

    // public function sendMessageCleora()
    // {
    //     $yesterday = now()->subDay();
    //     $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

    //     $yesterdayData = Order::whereDate('date', $yesterday)
    //         ->where('tenant_id', 1)
    //         ->selectRaw('SUM(amount) as turnover')
    //         ->first(); 

    //     $orderData = Order::whereDate('date', $yesterday)
    //         ->where('tenant_id', 1)
    //         ->selectRaw('COUNT(id) as transactions, COUNT(DISTINCT customer_phone_number) as customers')
    //         ->first();

    //     $avgTurnoverPerTransaction = $orderData->transactions > 0 
    //         ? round($yesterdayData->turnover / $orderData->transactions, 2) 
    //         : 0;

    //     $avgTurnoverPerCustomer = $orderData->customers > 0 
    //         ? round($yesterdayData->turnover / $orderData->customers, 2) 
    //         : 0;

    //     // Format daily turnover
    //     $formattedTurnover = number_format($yesterdayData->turnover, 0, ',', '.');
    //     $formattedAvgPerTransaction = number_format($avgTurnoverPerTransaction, 0, ',', '.');
    //     $formattedAvgPerCustomer = number_format($avgTurnoverPerCustomer, 0, ',', '.');

    //     $startOfMonth = now()->startOfMonth();
    //     $thisMonthData = Order::whereBetween('date', [$startOfMonth, $yesterday])
    //         ->where('tenant_id', 1)
    //         ->selectRaw('SUM(amount) as total_turnover')
    //         ->first();

    //     $thisMonthOrderData = Order::whereBetween('date', [$startOfMonth, $yesterday])
    //         ->where('tenant_id', 1)
    //         ->selectRaw('COUNT(id) as total_transactions, COUNT(DISTINCT customer_phone_number) as total_customers')
    //         ->first();

    //     $formattedMonthTurnover = number_format($thisMonthData->total_turnover, 0, ',', '.');
    //     $formattedMonthTransactions = number_format($thisMonthOrderData->total_transactions, 0, ',', '.');
    //     $formattedMonthCustomers = number_format($thisMonthOrderData->total_customers, 0, ',', '.');

    //     $daysPassed = now()->day - 1;
    //     $remainingDays = now()->daysInMonth - $daysPassed;

    //     $avgDailyTurnover = $daysPassed > 0 ? $thisMonthData->total_turnover / $daysPassed : 0;
    //     $avgDailyTransactions = $daysPassed > 0 ? $thisMonthOrderData->total_transactions / $daysPassed : 0;
    //     $avgDailyCustomers = $daysPassed > 0 ? $thisMonthOrderData->total_customers / $daysPassed : 0;

    //     $projectedTurnover = $thisMonthData->total_turnover + ($avgDailyTurnover * $remainingDays);
    //     $projectedTransactions = $thisMonthOrderData->total_transactions + ($avgDailyTransactions * $remainingDays);
    //     $projectedCustomers = $thisMonthOrderData->total_customers + ($avgDailyCustomers * $remainingDays);

    //     // Format projections
    //     $formattedProjectedTurnover = number_format($projectedTurnover, 0, ',', '.');
    //     $formattedProjectedTransactions = number_format($projectedTransactions, 0, ',', '.');
    //     $formattedProjectedCustomers = number_format($projectedCustomers, 0, ',', '.');

    //     // Calculate turnover per sales channel
    //     $salesChannelData = Order::whereDate('date', $yesterday)
    //         ->where('tenant_id', 1)
    //         ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
    //         ->groupBy('sales_channel_id')
    //         ->get();

    //     // Monthly data per sales channel for projection
    //     $thisMonthSalesChannelData = Order::whereBetween('date', [$startOfMonth, $yesterday])
    //         ->where('tenant_id', 1)
    //         ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
    //         ->groupBy('sales_channel_id')
    //         ->get();

    //     // Map sales channel data to names and calculate projections
    //     $salesChannelNames = SalesChannel::pluck('name', 'id');
    //     $salesChannelTurnover = $salesChannelData->map(function ($item) use ($salesChannelNames) {
    //         $channelName = $salesChannelNames->get($item->sales_channel_id);
    //         $formattedAmount = number_format($item->total_amount, 0, ',', '.');
    //         return "{$channelName}: Rp {$formattedAmount}";
    //     })->implode("\n");

    //     $totalProjectedTurnover = $thisMonthSalesChannelData->reduce(function ($carry, $item) use ($daysPassed, $remainingDays) {
    //         $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
    //         return $carry + $item->total_amount + ($dailyAverage * $remainingDays);
    //     }, 0);
        
    //     $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($salesChannelNames, $daysPassed, $remainingDays, $totalProjectedTurnover) {
    //         $channelName = $salesChannelNames->get($item->sales_channel_id);
    //         $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
    //         $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
    //         $percentage = $totalProjectedTurnover > 0 ? round(($projectedAmount / $totalProjectedTurnover) * 100, 2) : 0;
    //         $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
    //         return "{$channelName}: Rp {$formattedProjectedAmount} ({$percentage}%)";
    //     })->implode("\n");

    //     $startOfLastMonth = now()->subMonth()->startOfMonth();
    //     $endOfLastMonth = now()->subMonth()->startOfMonth()->addDays(now()->day - 1);

    //     $lastMonthData = Sales::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
    //         ->where('tenant_id', 1)
    //         ->selectRaw('SUM(turnover) as total_turnover')
    //         ->first();

    //     $growthMTDLM = $lastMonthData->total_turnover > 0
    //         ? round((($thisMonthData->total_turnover - $lastMonthData->total_turnover) / $lastMonthData->total_turnover) * 100, 2)
    //         : 0;
        
    //     $dayBeforeYesterday = now()->subDays(2);

    //     $dayBeforeYesterdayData = Sales::whereDate('date', $dayBeforeYesterday)
    //         ->where('tenant_id', 1)
    //         ->select('turnover')
    //         ->first();

    //     $growthYesterdayPast2Days = $dayBeforeYesterdayData && $dayBeforeYesterdayData->turnover > 0
    //         ? round((($yesterdayData->turnover - $dayBeforeYesterdayData->turnover) / $dayBeforeYesterdayData->turnover) * 100, 2)
    //         : 0;

    //     $message = <<<EOD
    //     🔥Laporan Transaksi CLEORA🔥
    //     Periode: $yesterdayDateFormatted

    //     📅 Kemarin
    //     Total Omzet: Rp {$formattedTurnover}
    //     Total Transaksi: {$orderData->transactions}
    //     Total Customer: {$orderData->customers}
    //     Avg Rp/Trx: Rp {$formattedAvgPerTransaction}
    //     Growth(Yesterday/Past 2 Days): {$growthYesterdayPast2Days}%

    //     📅 Bulan Ini
    //     Total Omzet: Rp {$formattedMonthTurnover}
    //     Total Transaksi: {$formattedMonthTransactions}
    //     Total Customer: {$formattedMonthCustomers}
    //     Growth(MTD/LM) : {$growthMTDLM}%

    //     📈 Proyeksi Bulan Ini
    //     Proyeksi Omzet: Rp {$formattedProjectedTurnover}
    //     Proyeksi Total Transaksi: {$formattedProjectedTransactions}
    //     Proyeksi Total Customer: {$formattedProjectedCustomers}

    //     📈 Omset Sales Channel Kemarin
    //     {$salesChannelTurnover}

    //     📈 Proyeksi Sales Channel
    //     {$salesChannelProjection}
    //     EOD;

    //     $response = $this->telegramService->sendMessage($message);
    //     return response()->json($response);
    // }
    public function sendMessageCleora()
    {
        $yesterday = now()->subDay();
        $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

        // Get NetProfit data for yesterday
        $yesterdayNetProfit = NetProfit::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->first();
        
        // Get Sales data for yesterday
        $yesterdaySales = Sales::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->first();
        
        // Calculate metrics from NetProfit
        $sales = $yesterdayNetProfit ? $yesterdayNetProfit->sales : 0;
        $formattedSales = number_format($sales, 0, ',', '.');
        
        $penjualanBersih = $yesterdayNetProfit ? $yesterdayNetProfit->sales * 0.85 : 0;
        $formattedPenjualanBersih = number_format($penjualanBersih, 0, ',', '.');
        
        $totalMarketingSpend = $yesterdayNetProfit ? ($yesterdayNetProfit->marketing + $yesterdayNetProfit->spent_kol + ($yesterdayNetProfit->affiliate ?? 0)) : 0;
        $formattedTotalMarketingSpend = number_format($totalMarketingSpend, 0, ',', '.');
        
        $romi = $totalMarketingSpend > 0 && $yesterdayNetProfit ? ($yesterdayNetProfit->sales / $totalMarketingSpend) : 0;
        $formattedRomi = number_format($romi, 2);
        
        $netProfit = $yesterdayNetProfit ? 
            (($yesterdayNetProfit->sales * 0.78) - 
            ($yesterdayNetProfit->marketing * 1.05) - 
            $yesterdayNetProfit->spent_kol - 
            ($yesterdayNetProfit->affiliate ?? 0) - 
            $yesterdayNetProfit->operasional - 
            $yesterdayNetProfit->hpp) : 0;
        $formattedNetProfit = number_format($netProfit, 0, ',', '.');
        
        $estimasiFeeAdmin = $yesterdayNetProfit ? ($yesterdayNetProfit->sales * 0.16) : 0;
        $formattedFeeAdmin = number_format($estimasiFeeAdmin, 0, ',', '.');
        
        $ppn = $yesterdayNetProfit ? ($yesterdayNetProfit->sales * 0.03) : 0;
        $formattedPpn = number_format($ppn, 0, ',', '.');
        
        $feeAds = $yesterdayNetProfit ? ($yesterdayNetProfit->marketing * 0.02) : 0;
        $formattedFeeAds = number_format($feeAds, 0, ',', '.');
        
        $feePacking = $yesterdayNetProfit ? ($yesterdayNetProfit->fee_packing ?? 0) : 0;
        $formattedFeePacking = number_format($feePacking, 0, ',', '.');
        
        $adSpentSocialMedia = $yesterdaySales ? ($yesterdaySales->ad_spent_social_media ?? 0) : 0;
        $formattedAdSpentSocialMedia = number_format($adSpentSocialMedia, 0, ',', '.');
        
        $adSpentMarketPlace = $yesterdaySales ? ($yesterdaySales->ad_spent_market_place ?? 0) : 0;
        $formattedAdSpentMarketPlace = number_format($adSpentMarketPlace, 0, ',', '.');
        
        $visit = $yesterdayNetProfit ? ($yesterdayNetProfit->visit ?? 0) : 0;
        $formattedVisit = number_format($visit, 0);
        
        $qty = $yesterdayNetProfit ? ($yesterdayNetProfit->qty ?? 0) : 0;
        $formattedQty = number_format($qty, 0);
        
        $order = $yesterdayNetProfit ? ($yesterdayNetProfit->order ?? 0) : 0;
        $formattedOrder = number_format($order, 0);
        
        $closingRate = $yesterdayNetProfit ? ($yesterdayNetProfit->closing_rate ?? 0) : 0;
        $formattedClosingRate = number_format($closingRate, 2) . '%';
        
        $roas = $yesterdayNetProfit ? ($yesterdayNetProfit->roas ?? 0) : 0;
        $formattedRoas = number_format($roas, 2);

        // Monthly data
        $startOfMonth = now()->startOfMonth();
        $thisMonthData = NetProfit::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('SUM(sales) as total_sales, SUM(marketing) as total_marketing, 
                        SUM(spent_kol) as total_spent_kol, SUM(affiliate) as total_affiliate,
                        SUM(operasional) as total_operasional, SUM(hpp) as total_hpp')
            ->first();

        $thisMonthSales = $thisMonthData->total_sales ?? 0;
        $formattedMonthSales = number_format($thisMonthSales, 0, ',', '.');
        
        $thisMonthNetProfit = ($thisMonthSales * 0.78) - 
                            (($thisMonthData->total_marketing ?? 0) * 1.05) - 
                            ($thisMonthData->total_spent_kol ?? 0) - 
                            ($thisMonthData->total_affiliate ?? 0) - 
                            ($thisMonthData->total_operasional ?? 0) - 
                            ($thisMonthData->total_hpp ?? 0);
        $formattedMonthNetProfit = number_format($thisMonthNetProfit, 0, ',', '.');

        // Last month comparison data
        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->startOfMonth()->addDays(now()->day - 1);

        $lastMonthData = NetProfit::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->where('tenant_id', 1)
            ->selectRaw('SUM(sales) as total_sales')
            ->first();

        $lastMonthSales = $lastMonthData->total_sales ?? 0;
        $growthMTDLM = $lastMonthSales > 0
            ? round((($thisMonthSales - $lastMonthSales) / $lastMonthSales) * 100, 2)
            : 0;
        
        $dayBeforeYesterday = now()->subDays(2);
        $dayBeforeYesterdayData = NetProfit::whereDate('date', $dayBeforeYesterday)
            ->where('tenant_id', 1)
            ->first();

        $dayBeforeYesterdaySales = $dayBeforeYesterdayData ? $dayBeforeYesterdayData->sales : 0;
        $growthYesterdayPast2Days = $dayBeforeYesterdaySales > 0
            ? round((($sales - $dayBeforeYesterdaySales) / $dayBeforeYesterdaySales) * 100, 2)
            : 0;

        // Projections
        $daysPassed = now()->day - 1;
        $remainingDays = now()->daysInMonth - $daysPassed;

        $avgDailySales = $daysPassed > 0 ? $thisMonthSales / $daysPassed : 0;
        $projectedSales = $thisMonthSales + ($avgDailySales * $remainingDays);
        $formattedProjectedSales = number_format($projectedSales, 0, ',', '.');

        // Get sales channel data
        $salesChannelData = SalesChannel::join('orders', 'sales_channels.id', '=', 'orders.sales_channel_id')
            ->whereDate('orders.date', $yesterday)
            ->where('orders.tenant_id', 1)
            ->selectRaw('sales_channels.name, SUM(orders.amount) as total_amount')
            ->groupBy('sales_channels.id', 'sales_channels.name')
            ->get();

        $salesChannelTurnover = $salesChannelData->map(function ($item) {
            $formattedAmount = number_format($item->total_amount, 0, ',', '.');
            return "{$item->name}: Rp {$formattedAmount}";
        })->implode("\n");

        // Monthly data per sales channel for projection
        $thisMonthSalesChannelData = SalesChannel::join('orders', 'sales_channels.id', '=', 'orders.sales_channel_id')
            ->whereBetween('orders.date', [$startOfMonth, $yesterday])
            ->where('orders.tenant_id', 1)
            ->selectRaw('sales_channels.id, sales_channels.name, SUM(orders.amount) as total_amount')
            ->groupBy('sales_channels.id', 'sales_channels.name')
            ->get();

        $totalProjectedTurnover = $thisMonthSalesChannelData->reduce(function ($carry, $item) use ($daysPassed, $remainingDays) {
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            return $carry + $item->total_amount + ($dailyAverage * $remainingDays);
        }, 0);
        
        $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($daysPassed, $remainingDays, $totalProjectedTurnover) {
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
            $percentage = $totalProjectedTurnover > 0 ? round(($projectedAmount / $totalProjectedTurnover) * 100, 2) : 0;
            $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
            return "{$item->name}: Rp {$formattedProjectedAmount} ({$percentage}%)";
        })->implode("\n");

        $message = <<<EOD
        🔥Laporan Transaksi CLEORA🔥
        Periode: $yesterdayDateFormatted

        📅 Kemarin
        Total Omzet: Rp {$formattedSales}
        Penjualan Bersih: Rp {$formattedPenjualanBersih}
        Net Profit: Rp {$formattedNetProfit}
        Marketing Spend: Rp {$formattedTotalMarketingSpend}
        ROMI: {$formattedRomi}
        Growth(Yesterday/Past 2 Days): {$growthYesterdayPast2Days}%

        💰 Biaya & Fee
        Fee Admin: Rp {$formattedFeeAdmin}
        PPN: Rp {$formattedPpn}
        Fee Ads: Rp {$formattedFeeAds}
        Fee Packing: Rp {$formattedFeePacking}

        📱 Ad Performance
        Ad Spent Social Media: Rp {$formattedAdSpentSocialMedia}
        Ad Spent Marketplace: Rp {$formattedAdSpentMarketPlace}
        Visitors: {$formattedVisit}
        Orders: {$formattedOrder}
        Quantity: {$formattedQty}
        Closing Rate: {$formattedClosingRate}
        ROAS: {$formattedRoas}

        📅 Bulan Ini
        Total Omzet: Rp {$formattedMonthSales}
        Net Profit: Rp {$formattedMonthNetProfit}
        Growth(MTD/LM): {$growthMTDLM}%

        📈 Proyeksi Bulan Ini
        Proyeksi Omzet: Rp {$formattedProjectedSales}

        📈 Omset Sales Channel Kemarin
        {$salesChannelTurnover}

        📈 Proyeksi Sales Channel
        {$salesChannelProjection}
        EOD;

        $response = $this->telegramService->sendMessage($message);
        return response()->json($response);
    }

    public function sendMessageAzrina()
    {
        $yesterday = now()->subDay();
        $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

        $yesterdayData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 2)
            ->selectRaw('SUM(amount) as turnover')
            ->first(); 

        $orderData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 2)
            ->selectRaw('COUNT(id) as transactions, COUNT(DISTINCT customer_phone_number) as customers')
            ->first();

        // Average turnover per transaction and per customer
        $avgTurnoverPerTransaction = $orderData->transactions > 0 
            ? round($yesterdayData->turnover / $orderData->transactions, 2) 
            : 0;

        $avgTurnoverPerCustomer = $orderData->customers > 0 
            ? round($yesterdayData->turnover / $orderData->customers, 2) 
            : 0;

        $formattedTurnover = number_format($yesterdayData->turnover, 0, ',', '.');
        $formattedAvgPerTransaction = number_format($avgTurnoverPerTransaction, 0, ',', '.');
        $formattedAvgPerCustomer = number_format($avgTurnoverPerCustomer, 0, ',', '.');

        $startOfMonth = now()->startOfMonth();
        $thisMonthData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 2)
            ->selectRaw('SUM(amount) as total_turnover')
            ->first();

        $thisMonthOrderData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 2)
            ->selectRaw('COUNT(id) as total_transactions, COUNT(DISTINCT customer_phone_number) as total_customers')
            ->first();

        $formattedMonthTurnover = number_format($thisMonthData->total_turnover, 0, ',', '.');
        $formattedMonthTransactions = number_format($thisMonthOrderData->total_transactions, 0, ',', '.');
        $formattedMonthCustomers = number_format($thisMonthOrderData->total_customers, 0, ',', '.');

        $daysPassed = now()->day - 1;
        $remainingDays = now()->daysInMonth - $daysPassed;

        $avgDailyTurnover = $daysPassed > 0 ? $thisMonthData->total_turnover / $daysPassed : 0;
        $avgDailyTransactions = $daysPassed > 0 ? $thisMonthOrderData->total_transactions / $daysPassed : 0;
        $avgDailyCustomers = $daysPassed > 0 ? $thisMonthOrderData->total_customers / $daysPassed : 0;

        $projectedTurnover = $thisMonthData->total_turnover + ($avgDailyTurnover * $remainingDays);
        $projectedTransactions = $thisMonthOrderData->total_transactions + ($avgDailyTransactions * $remainingDays);
        $projectedCustomers = $thisMonthOrderData->total_customers + ($avgDailyCustomers * $remainingDays);

        $formattedProjectedTurnover = number_format($projectedTurnover, 0, ',', '.');
        $formattedProjectedTransactions = number_format($projectedTransactions, 0, ',', '.');
        $formattedProjectedCustomers = number_format($projectedCustomers, 0, ',', '.');

        $salesChannelData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 2)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        $thisMonthSalesChannelData = Order::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 2)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        $salesChannelNames = SalesChannel::pluck('name', 'id');
        $salesChannelTurnover = $salesChannelData->map(function ($item) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $formattedAmount = number_format($item->total_amount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedAmount}";
        })->implode("\n");

        $totalProjectedTurnover = $thisMonthSalesChannelData->reduce(function ($carry, $item) use ($daysPassed, $remainingDays) {
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            return $carry + $item->total_amount + ($dailyAverage * $remainingDays);
        }, 0);
        
        $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($salesChannelNames, $daysPassed, $remainingDays, $totalProjectedTurnover) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
            $percentage = $totalProjectedTurnover > 0 ? round(($projectedAmount / $totalProjectedTurnover) * 100, 2) : 0;
            $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedProjectedAmount} ({$percentage}%)";
        })->implode("\n");

        $startOfLastMonth = now()->subMonth()->startOfMonth();
        $endOfLastMonth = now()->subMonth()->startOfMonth()->addDays(now()->day - 1);

        $lastMonthData = Sales::whereBetween('date', [$startOfLastMonth, $endOfLastMonth])
            ->where('tenant_id', 2)
            ->selectRaw('SUM(turnover) as total_turnover')
            ->first();

        $growthMTDLM = $lastMonthData->total_turnover > 0
            ? round((($thisMonthData->total_turnover - $lastMonthData->total_turnover) / $lastMonthData->total_turnover) * 100, 2)
            : 0;
        
        $dayBeforeYesterday = now()->subDays(2);

        $dayBeforeYesterdayData = Sales::whereDate('date', $dayBeforeYesterday)
            ->where('tenant_id', 2)
            ->select('turnover')
            ->first();

        $growthYesterdayPast2Days = $dayBeforeYesterdayData && $dayBeforeYesterdayData->turnover > 0
            ? round((($yesterdayData->turnover - $dayBeforeYesterdayData->turnover) / $dayBeforeYesterdayData->turnover) * 100, 2)
            : 0;

        $message = <<<EOD
        🫧 Laporan Transaksi AZRINA 🫧
        Periode: $yesterdayDateFormatted

        📅 Kemarin
        Total Omzet: Rp {$formattedTurnover}
        Total Transaksi: {$orderData->transactions}
        Total Customer: {$orderData->customers}
        Avg Rp/Trx: Rp {$formattedAvgPerTransaction}
        Growth(Yesterday/Past 2 Days): {$growthYesterdayPast2Days}%

        📅 Bulan Ini
        Total Omzet: Rp {$formattedMonthTurnover}
        Total Transaksi: {$formattedMonthTransactions}
        Total Customer: {$formattedMonthCustomers}
        Growth(MTD/LM) : {$growthMTDLM}%

        📈 Proyeksi Bulan Ini
        Proyeksi Omzet: Rp {$formattedProjectedTurnover}
        Proyeksi Total Transaksi: {$formattedProjectedTransactions}
        Proyeksi Total Customer: {$formattedProjectedCustomers}

        📈 Omset Sales Channel Kemarin
        {$salesChannelTurnover}

        📈 Proyeksi Sales Channel
        {$salesChannelProjection}
        EOD;

        $response = $this->telegramService->sendMessage($message);
        return response()->json($response);
    }

    public function sendMessageMarketingCleora()
    {
        $yesterday = now()->subDay();
        $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

        // Data kunjungan kemarin
        $visitData = Visit::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('SUM(visit_amount) as total_visits')
            ->first();

        $visitByChannel = Visit::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(visit_amount) as total_visits')
            ->groupBy('sales_channel_id')
            ->get();

        // Data transaksi dan omzet kemarin
        $yesterdayData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('SUM(amount) as turnover, COUNT(id) as transactions')
            ->first();

        $conversionRate = $visitData->total_visits > 0 
            ? round(($yesterdayData->transactions / $visitData->total_visits) * 100, 2) 
            : 0;

        // Pengeluaran iklan kemarin
        $socialMediaSpends = AdSpentSocialMedia::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('social_media_id, SUM(amount) as total_amount')
            ->groupBy('social_media_id')
            ->get();

        $marketplaceSpends = AdSpentMarketPlace::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        $totalAdsSpend = $socialMediaSpends->sum('total_amount') + $marketplaceSpends->sum('total_amount');
        $roas = $totalAdsSpend > 0 
            ? round($yesterdayData->turnover / $totalAdsSpend, 2) 
            : 0;

        $socialMediaNames = SocialMedia::pluck('name', 'id');
        $salesChannelNames = SalesChannel::pluck('name', 'id');

        $detailSocialMediaSpends = $socialMediaSpends->map(function ($spend) use ($socialMediaNames) {
            $platformName = $socialMediaNames->get($spend->social_media_id);
            $formattedAmount = number_format($spend->total_amount, 0, ',', '.');
            return "{$platformName}: Rp {$formattedAmount}";
        })->implode("\n");

        $detailMarketplaceSpends = $marketplaceSpends->map(function ($spend) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($spend->sales_channel_id);
            $formattedAmount = number_format($spend->total_amount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedAmount}";
        })->implode("\n");

        $detailVisitByChannel = $visitByChannel->map(function ($visit) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($visit->sales_channel_id);
            $formattedVisit = number_format($visit->total_visits, 0, ',', '.');
            return "{$channelName}: {$formattedVisit}";
        })->implode("\n");

        // Data bulan ini
        $startOfMonth = now()->startOfMonth();

        $monthlySocialMediaSpends = AdSpentSocialMedia::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('social_media_id, SUM(amount) as total_amount')
            ->groupBy('social_media_id')
            ->get();

        $monthlyMarketplaceSpends = AdSpentMarketPlace::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();

        $monthlyVisits = Visit::whereBetween('date', [$startOfMonth, $yesterday])
            ->where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(visit_amount) as total_visits')
            ->groupBy('sales_channel_id')
            ->get();

        $monthlyDetailSocialMediaSpends = $monthlySocialMediaSpends->map(function ($spend) use ($socialMediaNames) {
            $platformName = $socialMediaNames->get($spend->social_media_id);
            $formattedAmount = number_format($spend->total_amount, 0, ',', '.');
            return "{$platformName}: Rp {$formattedAmount}";
        })->implode("\n");

        $monthlyDetailMarketplaceSpends = $monthlyMarketplaceSpends->map(function ($spend) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($spend->sales_channel_id);
            $formattedAmount = number_format($spend->total_amount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedAmount}";
        })->implode("\n");

        $monthlyDetailVisits = $monthlyVisits->map(function ($visit) use ($salesChannelNames) {
            $channelName = $salesChannelNames->get($visit->sales_channel_id);
            $formattedVisit = number_format($visit->total_visits, 0, ',', '.');
            return "{$channelName}: {$formattedVisit}";
        })->implode("\n");

        $formattedOmzet = number_format($yesterdayData->turnover, 0, ',', '.');
        $formattedtotalAdsSpend = number_format($totalAdsSpend, 0, ',', '.');
        $formattedVisit = number_format($visitData->total_visits, 0, ',', '.');
        $formattedTransaction = number_format($yesterdayData->transactions, 0, ',', '.');

        $message = <<<EOD
        🔥 Laporan Marketing Cleora 🔥
        Periode $yesterdayDateFormatted

        📅 Kemarin
        Visit: {$formattedVisit}
        Transaksi: {$formattedTransaction}
        Conversion Rate: {$conversionRate}%
        Total Ads Spend: Rp {$formattedtotalAdsSpend}
        Omzet: Rp {$formattedOmzet}
        ROAS: {$roas}

        📈 Detail Ad Spent (Kemarin)
        {$detailSocialMediaSpends}
        {$detailMarketplaceSpends}

        📈 Detail Visit (Kemarin)
        {$detailVisitByChannel}

        📅 Ad Spent (Bulan ini)
        {$monthlyDetailSocialMediaSpends}
        {$monthlyDetailMarketplaceSpends}

        📈 Detail Visit (Bulan ini)
        {$monthlyDetailVisits}
        EOD;

        $response = $this->telegramService->sendMessage($message);
        return response()->json($response);
    }


    public function importFromGoogleSheet()
    {
        $this->googleSheetService->setSpreadsheetId('1LGAez9IydEKgLZwRnFX7_20T_hjgZ6tz3t-YXo4QBUw');
        $range = 'Import Sales!A2:Q';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            if (empty($row) || empty($row[0])) {
                continue;
            }
            $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
            if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                continue;
            }
            $salesChannelData = [
                4 => $row[8] ?? null, // Tiktok Shop (sales_channel_id == 4)
                1 => $row[10] ?? null, // Shopee (sales_channel_id == 1)
                3 => $row[11] ?? null, // Tokopedia (sales_channel_id == 3)
                2 => $row[12] ?? null, // Lazada (sales_channel_id == 2)
                8 => $row[13] ?? null, // Lazada (sales_channel_id == 2)
                9 => $row[14] ?? null, // Lazada (sales_channel_id == 2)
            ];

            foreach ($salesChannelData as $salesChannelId => $amountValue) {
                if (!isset($amountValue)) {
                    continue;
                }
                $amount = $this->parseCurrencyToInt($amountValue);

                AdSpentMarketPlace::updateOrCreate(
                    [
                        'date'             => $date,
                        'sales_channel_id' => $salesChannelId,
                        'tenant_id'        => $tenant_id,
                    ],
                    [
                        'amount'           => $amount,
                    ]
                );
            }

            // Social Media data
            $socialMediaData = [
                1 => $row[9] ?? null, // Facebook (social_media_id == 1)
                9 => $row[15] ?? null, // Facebook (social_media_id == 1)
                10 => $row[16] ?? null, // Facebook (social_media_id == 1)
                // 2 => $row[6] ?? null, // Snack Video (social_media_id == 2)
                // 5 => $row[7] ?? null, // Google Ads (social_media_id == 5)
            ];

            foreach ($socialMediaData as $socialMediaId => $amountValue) {
                if (!isset($amountValue)) {
                    continue;
                }
                $amount = $this->parseCurrencyToInt($amountValue);

                AdSpentSocialMedia::updateOrCreate(
                    [
                        'date'            => $date,
                        'social_media_id' => $socialMediaId,
                        'tenant_id'       => $tenant_id,
                    ],
                    [
                        'amount'          => $amount,
                    ]
                );
            }
        }

        return response()->json(['message' => 'Data imported successfully']);
    }

    public function importAdsAzrina()
    {
        $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
        $range = 'Azrina!A2:L';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            if (empty($row) || empty($row[0])) {
                continue;
            }
            
            try {
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                
                // Map marketplace ads data according to the provided terms
                // H=column 7, I=column 8, J=column 9, K=column 10, L=column 11
                $salesChannelData = [
                    1 => $row[9] ?? null,  // Shopee (column J)
                    3 => $row[10] ?? null, // Tokopedia (column K)
                    2 => $row[11] ?? null, // Lazada (column L)
                    4 => $row[7] ?? null,
                ];

                foreach ($salesChannelData as $salesChannelId => $amountValue) {
                    if (!isset($amountValue) || empty($amountValue)) {
                        continue;
                    }
                    
                    $amount = $this->parseCurrencyToInt($amountValue);

                    AdSpentMarketPlace::updateOrCreate(
                        [
                            'date'             => $date,
                            'sales_channel_id' => $salesChannelId,
                            'tenant_id'        => $tenant_id,
                        ],
                        [
                            'amount'           => $amount,
                        ]
                    );
                }

                // Map social media ads data according to the provided terms
                $socialMediaData = [
                    1 => $row[8] ?? null,  // Meta ads (column I)
                ];

                foreach ($socialMediaData as $socialMediaId => $amountValue) {
                    if (!isset($amountValue) || empty($amountValue)) {
                        continue;
                    }
                    
                    $amount = $this->parseCurrencyToInt($amountValue);

                    AdSpentSocialMedia::updateOrCreate(
                        [
                            'date'            => $date,
                            'social_media_id' => $socialMediaId,
                            'tenant_id'       => $tenant_id,
                        ],
                        [
                            'amount'          => $amount,
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log any date parsing errors or other exceptions
                \Log::error('Error importing ads data: ' . $e->getMessage());
                continue; // Skip this row and continue with the next
            }
        }

        return response()->json(['message' => 'Azrina ads data imported successfully']);
    }

    public function importVisitCleora()
    {
        $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
        $range = 'Cleora!A2:H'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            if (empty($row) || empty($row[0])) {
                continue;
            }
            $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
            if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                continue;
            }
            $salesChannelData = [
                1 => $row[4] ?? null,
                4 => $row[5] ?? null,
                2 => $row[6] ?? null, 
                3 => $row[7] ?? null, 
            ];

            foreach ($salesChannelData as $salesChannelId => $amountValue) {
                if (!isset($amountValue)) {
                    continue;
                }
                $amount = $this->parseCurrencyToInt($amountValue);

                Visit::updateOrCreate(
                    [
                        'date'             => $date,
                        'sales_channel_id' => $salesChannelId,
                        'tenant_id'        => $tenant_id,
                    ],
                    [
                        'visit_amount'           => $amount,
                    ]
                );
            }
        }
        return response()->json(['message' => 'Data imported successfully']);
    }
    public function importVisitAzrina()
    {
        // Set the spreadsheet ID to the new one
        $this->googleSheetService->setSpreadsheetId('1sDhPAvqXkBE3m2n1yt2ghFROygTxKx1gLiBnUkb26Q0');
        
        // Update range to match new sheet name and include columns up to G
        $range = 'Azrina!A2:G'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            // Skip rows without date in column A
            if (!isset($row[0]) || empty($row[0])) {
                continue;
            }
            
            try {
                $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
                
                // Skip rows not from current month
                if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                    continue;
                }
                
                // Map columns to sales channels according to provided terms
                // D = Shopee (index 3), E = TikTok (index 4), F = Lazada (index 5), G = Tokopedia (index 6)
                $salesChannelData = [
                    1 => $row[3] ?? null, // Shopee (column D)
                    2 => $row[4] ?? null, // TikTok (column E)
                    3 => $row[5] ?? null, // Lazada (column F)
                    4 => $row[6] ?? null, // Tokopedia (column G)
                ];

                foreach ($salesChannelData as $salesChannelId => $amountValue) {
                    if (!isset($amountValue) || empty($amountValue)) {
                        continue;
                    }
                    
                    $amount = $this->parseCurrencyToInt($amountValue);

                    Visit::updateOrCreate(
                        [
                            'date'             => $date,
                            'sales_channel_id' => $salesChannelId,
                            'tenant_id'        => $tenant_id,
                        ],
                        [
                            'visit_amount'     => $amount,
                        ]
                    );
                }
            } catch (\Exception $e) {
                // Log any date parsing errors or other exceptions
                \Log::error('Error importing visit data: ' . $e->getMessage());
                continue; // Skip this row and continue with the next
            }
        }
        
        return response()->json(['message' => 'Data imported successfully']);
    }


    /**
     * Helper function to parse currency string to integer
     */
    private function parseCurrencyToInt($currency)
    {
        return (int) str_replace(['Rp', '.', ','], '', $currency);
    }

    public function updateMonthlyAdSpentData()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');

                $sumSpentSocialMedia = AdSpentSocialMedia::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->sum('amount');

                $sumSpentMarketPlace = AdSpentMarketPlace::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->sum('amount');

                $totalAdSpent = $sumSpentSocialMedia + $sumSpentMarketPlace;

                $turnover = Sales::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->value('turnover');

                $roas = $totalAdSpent > 0 ? $turnover / $totalAdSpent : 0;
                
                $dataToUpdate = [
                    'ad_spent_social_media' => $sumSpentSocialMedia,
                    'ad_spent_market_place' => $sumSpentMarketPlace,
                    'ad_spent_total' => $totalAdSpent,
                    'roas' => $roas,
                ];
                
                Sales::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->update($dataToUpdate);
            }

            return response()->json(['status' => 'success', 'message' => 'Ad spent data updated for the current month.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMonthlyAdSpentDataAzrina()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');

                $sumSpentSocialMedia = AdSpentSocialMedia::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->where('social_media_id', '!=', 10)
                    ->sum('amount');

                $sumSpentMarketPlace = AdSpentMarketPlace::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->where('sales_channel_id', '!=', 9)
                    ->sum('amount');

                $totalAdSpent = $sumSpentSocialMedia + $sumSpentMarketPlace;

                $turnover = Sales::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->value('turnover');

                $roas = $totalAdSpent > 0 ? $turnover / $totalAdSpent : 0;
                
                $dataToUpdate = [
                    'ad_spent_social_media' => $sumSpentSocialMedia,
                    'ad_spent_market_place' => $sumSpentMarketPlace,
                    'ad_spent_total' => $totalAdSpent,
                    'roas' => $roas,
                ];
                
                Sales::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->update($dataToUpdate);
            }

            return response()->json(['status' => 'success', 'message' => 'Ad spent data updated for the current month.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function updateMonthlyVisitData()
    {
        try {
            $startOfMonth = Carbon::now()->startOfMonth();
            $endOfMonth = Carbon::now()->endOfMonth();

            for ($date = $startOfMonth; $date <= $endOfMonth; $date->addDay()) {
                $formattedDate = $date->format('Y-m-d');

                $sumVisitCleora = Visit::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->sum('visit_amount');

                $sumVisitAzrina = Visit::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->sum('visit_amount');
                
                $dataToUpdate = [
                    'visit' => $sumVisitCleora,
                ];
                Sales::where('tenant_id', 1)
                    ->where('date', $formattedDate)
                    ->update($dataToUpdate);

                $dataToUpdate = [
                    'visit' => $sumVisitAzrina,
                ];
                Sales::where('tenant_id', 2)
                    ->where('date', $formattedDate)
                    ->update($dataToUpdate);
            }

            return response()->json(['status' => 'success', 'message' => 'Ad spent data updated for the current month.']);
        } catch (Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    private $accessToken = 'EAAvb8cyWo24BO7WfoZC7ROrDFa2lTz9hvMxaP9FZBZC6dOdiSE9LKpB8mGGJNZBwoupqVikudVuZBtB1BZAbkyBKeYsQFuM6JOuG0iexXpfznDIg9yWBwodIp06GF0VAYRtZAG3Sn4wZCkWkCMhVPPtZB8xFsthGtSDWfaOAtPgqy3SWL9T3qcPEl8g32StqUZAq54vSAZBQSUF';
    private $adAccountId = '545160191589598';

    public function getAdInsights()
    {
        $url = "https://graph.facebook.com/v13.0/act_{$this->adAccountId}/insights";

        // Define the query parameters
        $params = [
            'access_token' => $this->accessToken,
            'level' => 'account',
            'fields' => 'results,result_rate,reach,frequency,impressions', // Add more fields if needed
            'time_range' => json_encode([
                'since' => '2024-10-08',
                'until' => '2024-11-07',
            ]),
        ];

        // Send a GET request to the Meta API
        $response = Http::get($url, $params);

        // Check if the response is successful
        if ($response->successful()) {
            return response()->json([
                'data' => $response->json(),
                'message' => 'Insights retrieved successfully',
            ]);
        } else {
            return response()->json([
                'message' => 'Failed to retrieve insights',
                'error' => $response->json(),
            ], $response->status());
        }
    }

    public function getSalesChannelDonutData()
    {
        $salesChannelData = Order::where('tenant_id', 1)
            ->selectRaw('sales_channel_id, SUM(amount) as total_amount')
            ->groupBy('sales_channel_id')
            ->get();
        
        // Get sales channel names, excluding those with null or 'Others'
        $salesChannelNames = SalesChannel::whereNotNull('name')
            ->where('name', '!=', 'Others')
            ->pluck('name', 'id');

        $labels = [];
        $data = [];

        // Define the custom color mapping based on channel names
        $backgroundColors = [
            'Shopee' => '#EE4D2D', // Shopee orange
            'Lazada' => '#0F146D',  // Lazada blue
            'Tokopedia' => '#42B549', // Tokopedia green
            'Tiktok Shop' => '#000000', // Tiktok Shop black
            'Reseller' => '#FF6B6B', // Reseller red
            'Others' => '#6C757D',  // Default gray for others
        ];

        // Array to store the dynamically assigned colors
        $colors = [];

        // Loop through each sales channel data
        $salesChannelData->each(function ($item) use ($salesChannelNames, &$labels, &$data, &$backgroundColors, &$colors) {
            // Check if the channel name is valid (i.e., not null or 'Others')
            $channelName = $salesChannelNames->get($item->sales_channel_id);

            // Skip if the channel name is null or excluded
            if ($channelName === null) {
                return; // Continue to the next iteration
            }

            // Push the channel name and total amount into the arrays
            $labels[] = $channelName;
            $data[] = $item->total_amount;

            // Assign the color for the channel
            $colors[] = $backgroundColors[$channelName] ?? '#6C757D'; // Fallback to gray for unknown channels
        });
        
        // Return the JSON response
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors, // Use the dynamically generated colors
                    'hoverBackgroundColor' => $colors, // Optional: hover effect with same colors
                    'borderWidth' => 0, // Optional: remove borders between segments
                ]
            ]
        ]);
    }



    public function getTotalAdSpentForDonutChart(Request $request)
    {
            $query1 = AdSpentSocialMedia::where('tenant_id', Auth::user()->current_tenant_id);
            $query2 = AdSpentMarketPlace::where('tenant_id', Auth::user()->current_tenant_id);
            
            // Apply date range filter
            if ($request->filled('filterDates')) {
                $dates = explode(' - ', $request->filterDates);
                if (count($dates) == 2) {
                    $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
                    $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();
                    
                    $query1->whereBetween('date', [$startDate, $endDate]);
                    $query2->whereBetween('date', [$startDate, $endDate]);
                }
            }
            
            // Apply social media filter
            if ($request->filled('social_media_ids')) {
                $query1->whereIn('social_media_id', $request->social_media_ids);
            }
            
            // Apply marketplace filter
            if ($request->filled('marketplace_ids')) {
                $query2->whereIn('sales_channel_id', $request->marketplace_ids);
            }
            
            $socialMediaSpends = $query1->selectRaw('social_media_id, SUM(amount) as total_amount')
                ->groupBy('social_media_id')
                ->get();
                
            $marketplaceSpends = $query2->selectRaw('sales_channel_id, SUM(amount) as total_amount')
                ->groupBy('sales_channel_id')
                ->get();

        $socialMediaNames = SocialMedia::pluck('name', 'id');
        $salesChannelNames = SalesChannel::pluck('name', 'id');
        
        $labels = [];
        $data = [];
        $backgroundColor = [];
        $borderColor = [];

        // Define custom colors for social media and marketplace ads
        $socialMediaColors = [
            'Facebook' => '#4267B2',   // Facebook Blue
            'Twitter' => '#1DA1F2',    // Twitter Blue
            'Google Ads' => '#4285F4',
            'Snack Video' => '#FFDA00',
            'Meta' => '#4267B2',
            'Tiktok' => '#000000',

        ];

        $marketplaceColors = [
            'Shopee' => '#EE4D2D',      // Shopee Red
            'Lazada' => '#0F146D',      // Lazada Blue
            'Tokopedia' => '#42B549',   // Tokopedia Green
            'Tiktok Shop' => '#000000', // Tiktok Shop Black
            'Reseller' => '#FF6B6B',    // Reseller Red
            'Others' => '#6C757D'       // Default Gray
        ];

        // Assign colors for social media spends
        foreach ($socialMediaSpends as $spend) {
            $platformName = $socialMediaNames->get($spend->social_media_id);
            
            // Add to the chart data
            $labels[] = $platformName;
            $data[] = $spend->total_amount;

            // Set background color based on social media platform
            $backgroundColor[] = $socialMediaColors[$platformName] ?? '#FF6B6B';  // Default red if platform is not found
        }

        // Assign colors for marketplace spends
        foreach ($marketplaceSpends as $spend) {
            $channelName = $salesChannelNames->get($spend->sales_channel_id);
            
            // Add to the chart data
            $labels[] = $channelName;
            $data[] = $spend->total_amount;

            // Set background color based on marketplace
            $backgroundColor[] = $marketplaceColors[$channelName] ?? '#6C757D';  // Default gray if channel is not found
        }

        // Prepare the chart data
        $donutChartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $backgroundColor, // Use the dynamically assigned colors
                    'hoverBackgroundColor' => $backgroundColor, // Optional: hover effect with same colors
                    'borderWidth' => 0, // Optional: remove borders between segments
                ]
            ]
        ];

        return response()->json($donutChartData);
    }

    public function getTotalAmountPerSalesChannelPerMonth()
{
    // Define channel colors at the start
    $channelColors = [
        'Shopee' => '#EE4D2D',
        'Lazada' => '#0F146D',
        'Tokopedia' => '#42B549',
        'Tiktok Shop' => '#000000',
        'Reseller' => '#FF6B6B',
        'Others' => '#6C757D',
    ];

    // Fetch sales data without any filtering for current year/month
    $salesData = Order::selectRaw('
            YEAR(date) as year,
            MONTH(date) as month,
            sales_channel_id,
            SUM(amount) as total_amount
        ')
        ->where('tenant_id', 1)
        ->groupBy('year', 'month', 'sales_channel_id')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc') 
        ->get();

    // Fetch sales channel names
    $salesChannelNames = SalesChannel::whereNotNull('name')
        ->where('name', '!=', 'Others')
        ->pluck('name', 'id');

    // Prepare chart data
    $chartData = [
        'labels' => [],
        'datasets' => []
    ];

    $salesChannelsData = [];
    $monthsInOrder = [
        'January', 'February', 'March', 'April', 'May', 'June',
        'July', 'August', 'September', 'October', 'November', 'December'
    ];

    // Generate labels with month and year (All available months)
    foreach ($salesData as $data) {
        $month = date('F', strtotime("{$data->year}-{$data->month}-01"));
        $monthLabel = "{$month} {$data->year}";
        if (!in_array($monthLabel, $chartData['labels'])) {
            $chartData['labels'][] = $monthLabel;
        }
    }

    foreach ($salesData as $data) {
        if (!$salesChannelNames->has($data->sales_channel_id)) {
            continue;
        }

        $channelName = $salesChannelNames->get($data->sales_channel_id);
        
        if ($channelName === null) {
            continue;
        }

        // Get the correct month label (e.g., January 2025)
        $month = date('F', strtotime("{$data->year}-{$data->month}-01"));
        $year = $data->year;
        $monthLabel = "{$month} {$year}";
        $monthIndex = array_search($monthLabel, $chartData['labels']);  // Find the index based on "Month Year"

        // Initialize data for each sales channel
        if (!isset($salesChannelsData[$data->sales_channel_id])) {
            $salesChannelsData[$data->sales_channel_id] = [
                'label' => $channelName,
                'data' => array_fill(0, count($chartData['labels']), 0),
                'fill' => false,
                'borderColor' => $channelColors[$channelName] ?? '#6C757D',
                'backgroundColor' => ($channelColors[$channelName] ?? '#6C757D') . '20',
                'tension' => 0.4
            ];
        }

        // Assign the total amount to the correct month index
        if ($monthIndex !== false) {
            $salesChannelsData[$data->sales_channel_id]['data'][$monthIndex] = $data->total_amount;
        }
    }

    $salesChannelsData = array_filter($salesChannelsData, function($channelData) {
        return $channelData['label'] !== null;
    });

    // Prepare the final datasets for the chart
    foreach ($salesChannelsData as $channelData) {
        $chartData['datasets'][] = $channelData;
    }

    return response()->json($chartData);
}


    public function getWaterfallData()
    {
        $sales = Sales::selectRaw('
                date,
                SUM(turnover) as turnover,
                SUM(ad_spent_total) as ad_spent_total
            ')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $response = [];
        $weeklyTotal = 0;
        $dayCounter = 0;
        $weekCounter = 1;

        foreach ($sales as $index => $sale) {
            $dayCounter++;
            $dailyNet = $sale->turnover - $sale->ad_spent_total;
            $weeklyTotal += $dailyNet;

            // Add daily data
            $response[] = [
                'date' => date('Y-m-d', strtotime($sale->date)),
                'turnover' => (int)$sale->turnover,
                'ad_spent' => (int)$sale->ad_spent_total,
                'net' => $dailyNet
            ];

            // Add weekly total after every 7 days or at the end
            if ($dayCounter % 7 === 0 || $index === count($sales) - 1) {
                $response[] = [
                    'date' => "Week {$weekCounter} Total",
                    'turnover' => 0,
                    'ad_spent' => 0,
                    'net' => $weeklyTotal,
                    'is_weekly_total' => true
                ];
                $weeklyTotal = 0;
                $weekCounter++;
            }
        }

        return response()->json($response);
    }
    public function getWaterfallData2()
    {
        $sales = Sales::selectRaw('
                date,
                SUM(turnover) as turnover,
                SUM(ad_spent_total) as ad_spent_total
            ')
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $response = [];
        $weeklyTotal = 0;
        $dayCounter = 0;
        $weekCounter = 1;

        foreach ($sales as $index => $sale) {
            // Calculate HPP for this date
            $skuCounts = [];
            
            DB::table('orders')
                ->select('sku')
                ->whereDate('date', $sale->date)
                ->whereNotIn('status', ['pending', 'cancelled', 'request_cancel', 'request_return'])
                ->orderBy('id')
                ->chunk(1000, function($orders) use (&$skuCounts) {
                    foreach ($orders as $order) {
                        $skuItems = explode(',', $order->sku);
                        
                        foreach ($skuItems as $item) {
                            $item = trim($item);
                            
                            if (preg_match('/^(\d+)\s+(.+)$/', $item, $matches)) {
                                $quantity = (int)$matches[1];
                                $skuCode = trim($matches[2]);
                            } else {
                                $quantity = 1;
                                $skuCode = trim($item);
                            }
                            
                            if (!isset($skuCounts[$skuCode])) {
                                $skuCounts[$skuCode] = 0;
                            }
                            $skuCounts[$skuCode] += $quantity;
                        }
                    }
                });

            // Calculate total HPP for the day
            $dailyHPP = 0;
            foreach ($skuCounts as $sku => $quantity) {
                $product = DB::table('products')
                    ->select('harga_satuan')
                    ->where('sku', $sku)
                    ->first();
                    
                $harga_satuan = $product ? $product->harga_satuan : null;
                $hpp = $harga_satuan ? $harga_satuan * $quantity : 0;
                
                $dailyHPP += $hpp;
            }

            $dayCounter++;
            $dailyNet = $sale->turnover - $sale->ad_spent_total - $dailyHPP;
            $weeklyTotal += $dailyNet;

            $response[] = [
                'date' => date('Y-m-d', strtotime($sale->date)),
                'turnover' => (int)$sale->turnover,
                'ad_spent' => (int)$sale->ad_spent_total,
                'hpp' => (int)$dailyHPP,
                'net' => $dailyNet
            ];

            if ($dayCounter % 7 === 0 || $index === count($sales) - 1) {
                $response[] = [
                    'date' => "Week {$weekCounter} Total",
                    'turnover' => 0,
                    'ad_spent' => 0,
                    'hpp' => 0,
                    'net' => $weeklyTotal,
                    'is_weekly_total' => true
                ];
                $weeklyTotal = 0;
                $weekCounter++;
            }
        }

        return response()->json($response);
    }

    public function getMonthlySalesChart()
    {
        $currentMonth = now()->startOfMonth();
        
        $sales = Sales::select('date', 'turnover')
            ->whereYear('date', $currentMonth->year)
            ->whereMonth('date', $currentMonth->month)
            ->where('tenant_id', 1)
            ->orderBy('date')
            ->get();

        $labels = [];
        $turnoverData = [];

        foreach ($sales as $sale) {
            $labels[] = Carbon::parse($sale->date)->format('d M');
            $turnoverData[] = $sale->turnover;
        }

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Turnover',
                    'data' => $turnoverData,
                    'borderColor' => '#4CAF50',
                    'tension' => 0.1,
                    'fill' => false
                ]
            ]
        ]);
    }

    public function getTotalAdSpentPerSalesChannelAndSocialMedia(Request $request)
{
    $socialMediaColors = [
        'Facebook' => '#4267B2',
        'Twitter' => '#1DA1F2',
        'Google Ads' => '#4285F4',
        'Snack Video' => '#FFDA00',
        'Meta' => '#4267B2',
        'Tiktok' => '#000000',
    ];

    $marketplaceColors = [
        'Shopee' => '#EE4D2D',
        'Lazada' => '#0F146D',
        'Tokopedia' => '#42B549',
        'Tiktok Shop' => '#000000',
        'Reseller' => '#FF6B6B',
        'Others' => '#6C757D'
    ];

    // Initialize queries
    $query1 = AdSpentSocialMedia::where('tenant_id', Auth::user()->current_tenant_id);
    $query2 = AdSpentMarketPlace::where('tenant_id', Auth::user()->current_tenant_id);

    // Apply date filter
    if ($request->filled('filterDates')) {
        $dates = explode(' - ', $request->filterDates);
        if (count($dates) == 2) {
            $startDate = Carbon::createFromFormat('d/m/Y', $dates[0])->startOfDay();
            $endDate = Carbon::createFromFormat('d/m/Y', $dates[1])->endOfDay();
            
            $query1->whereBetween('date', [$startDate, $endDate]);
            $query2->whereBetween('date', [$startDate, $endDate]);
        }
    }

    // Apply social media filter
    if ($request->filled('social_media_ids')) {
        $query1->whereIn('social_media_id', $request->social_media_ids);
    }

    // Apply marketplace filter
    if ($request->filled('marketplace_ids')) {
        $query2->whereIn('sales_channel_id', $request->marketplace_ids);
    }

    // Get data with grouping
    $socialMediaAdSpends = $query1->selectRaw('
        YEAR(date) as year,
        MONTH(date) as month,
        social_media_id,
        SUM(amount) as total_amount
    ')
    ->groupBy('year', 'month', 'social_media_id')
    ->orderBy('year', 'asc')
    ->orderBy('month', 'asc')
    ->get();

    $marketplaceAdSpends = $query2->selectRaw('
        YEAR(date) as year,
        MONTH(date) as month,
        sales_channel_id,
        SUM(amount) as total_amount
    ')
    ->groupBy('year', 'month', 'sales_channel_id')
    ->orderBy('year', 'asc')
    ->orderBy('month', 'asc')
    ->get();

    $socialMediaNames = SocialMedia::pluck('name', 'id');
    $salesChannelNames = SalesChannel::pluck('name', 'id');

    // Get date range from actual data, but cap at current year/month
    $currentYear = (int)date('Y');
    $currentMonth = (int)date('n');
    
    $firstYear = min(
        $socialMediaAdSpends->min('year') ?: $currentYear,
        $marketplaceAdSpends->min('year') ?: $currentYear
    );

    $chartData = [
        'labels' => [],
        'datasets' => []
    ];

    $salesChannelsData = [];
    $socialMediaData = [];
    $monthsInOrder = [];

    // Generate month/year combinations up to current month
    for ($year = $firstYear; $year <= $currentYear; $year++) {
        $maxMonth = ($year == $currentYear) ? $currentMonth : 12;
        for ($month = 1; $month <= $maxMonth; $month++) {
            $monthsInOrder[] = date('F Y', strtotime("{$year}-{$month}-01"));
        }
    }

    $chartData['labels'] = $monthsInOrder;

    foreach ($marketplaceAdSpends as $data) {
        if ($data->year > $currentYear || 
            ($data->year == $currentYear && $data->month > $currentMonth)) {
            continue;
        }

        $channelName = $salesChannelNames->get($data->sales_channel_id);
        $monthYear = date('F Y', strtotime("{$data->year}-{$data->month}-01"));
        $monthIndex = array_search($monthYear, $chartData['labels']);
        
        if (!isset($salesChannelsData[$data->sales_channel_id])) {
            $salesChannelsData[$data->sales_channel_id] = [
                'label' => $channelName,
                'data' => array_fill(0, count($chartData['labels']), 0),
                'fill' => false,
                'borderColor' => $marketplaceColors[$channelName] ?? '#6C757D',
                'backgroundColor' => ($marketplaceColors[$channelName] ?? '#6C757D') . '20',
                'tension' => 0.4
            ];
        }

        if ($monthIndex !== false) {
            $salesChannelsData[$data->sales_channel_id]['data'][$monthIndex] = $data->total_amount;
        }
    }

    // Process social media ad spends
    foreach ($socialMediaAdSpends as $data) {
        // Skip data points beyond current month/year
        if ($data->year > $currentYear || 
            ($data->year == $currentYear && $data->month > $currentMonth)) {
            continue;
        }

        $platformName = $socialMediaNames->get($data->social_media_id);
        $monthYear = date('F Y', strtotime("{$data->year}-{$data->month}-01"));
        $monthIndex = array_search($monthYear, $chartData['labels']);
        
        if (!isset($socialMediaData[$data->social_media_id])) {
            $socialMediaData[$data->social_media_id] = [
                'label' => $platformName,
                'data' => array_fill(0, count($chartData['labels']), 0),
                'fill' => false,
                'borderColor' => $socialMediaColors[$platformName] ?? '#6C757D',
                'backgroundColor' => ($socialMediaColors[$platformName] ?? '#6C757D') . '20',
                'tension' => 0.4
            ];
        }

        if ($monthIndex !== false) {
            $socialMediaData[$data->social_media_id]['data'][$monthIndex] = $data->total_amount;
        }
    }

    // Merge datasets
    foreach ($salesChannelsData as $channelData) {
        $chartData['datasets'][] = $channelData;
    }

    foreach ($socialMediaData as $platformData) {
        $chartData['datasets'][] = $platformData;
    }

    return response()->json($chartData);
}
    public function getOrderStatusSummary()
    {
        $startOfMonth = Carbon::now()->startOfMonth()->toDateString();
        $endOfMonth = Carbon::now()->endOfMonth()->toDateString();

        $validStatuses = ['completed', 'sent', 'cancelled', 'pending', 'sent_booking', 'process'];

        // Get both the total amount and the count of orders for each status
        $orderStatusSummary = Order::select('status', 
            \DB::raw('SUM(amount) as total_amount'),
            \DB::raw('COUNT(id_order) as total_count')
        )
        ->whereBetween('date', [$startOfMonth, $endOfMonth])
        ->whereIn('status', $validStatuses)
        ->groupBy('status')
        ->get();

        // Prepare the response format to include both status, total_amount, and total_count
        $result = $orderStatusSummary->map(function ($item) {
            return [
                'status' => $item->status,
                'total_amount' => $item->total_amount,
                'total_count' => $item->total_count,
            ];
        });

        return response()->json($result);
    }

    public function getNetProfitMarginDaily(Request $request)
    {
        $query = NetProfit::query()
            ->select([
                'date',
                DB::raw('CAST((sales * 0.78) - (marketing * 1.05) - spent_kol - COALESCE(affiliate, 0) - operasional - hpp AS DECIMAL(15,2)) as net_profit_margin')
            ]);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        } else {
            $startDate = now()->startOfMonth();
            $endDate = now()->endOfMonth();
            $query->whereBetween('date', [$startDate, $endDate]);
        }

        return response()->json(
            $query->orderBy('date')
                ->get()
                ->map(fn($row) => [
                    'date' => date('Y-m-d', strtotime($row->date)),
                    'net' => (float)$row->net_profit_margin
                ])
        );
    }
}