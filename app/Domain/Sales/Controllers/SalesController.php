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
use App\Domain\Marketing\Models\SocialMedia;
use App\Domain\Sales\BLL\Visit\VisitBLLInterface;
use App\Domain\Sales\Models\Sales;
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

        // Assuming you have a Sales model and it's properly set up with relationships
        // Adjust the query according to your actual database schema

        $omsetData = Sales::whereDate('created_at', $date)
            ->groupBy('date')
            ->get();

        // Return the data as a JSON response
        return response()->json($omsetData);
    }
    public function sendMessageCleora()
    {
        // Get yesterday's data
        $yesterday = now()->subDay();
        $yesterdayDateFormatted = $yesterday->translatedFormat('l, d F Y');

        // Yesterday's sales and transaction data
        $yesterdayData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('SUM(amount) as turnover')
            ->first(); 

        $orderData = Order::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->selectRaw('COUNT(id) as transactions, COUNT(DISTINCT customer_phone_number) as customers')
            ->first();

        // Average turnover per transaction and per customer
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

        // Format monthly turnover
        $formattedMonthTurnover = number_format($thisMonthData->total_turnover, 0, ',', '.');
        $formattedMonthTransactions = number_format($thisMonthOrderData->total_transactions, 0, ',', '.');
        $formattedMonthCustomers = number_format($thisMonthOrderData->total_customers, 0, ',', '.');

        // Monthly projection
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

        $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($salesChannelNames, $daysPassed, $remainingDays) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
            $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedProjectedAmount}";
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
        
        $socialMediaSpends = AdSpentSocialMedia::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->where('amount', '>', 0)
            ->select('social_media_id', 'amount')
            ->get();
    
        $socialMediaNames = SocialMedia::pluck('name', 'id');
        $socialMediaSpendText = $socialMediaSpends->map(function ($spend) use ($socialMediaNames) {
            $platformName = $socialMediaNames->get($spend->social_media_id);
            $formattedAmount = number_format($spend->amount, 0, ',', '.');
            return "{$platformName}: Rp {$formattedAmount}";
        })->implode("\n");
    
        $marketplaceSpends = AdSpentMarketPlace::whereDate('date', $yesterday)
            ->where('tenant_id', 1)
            ->where('amount', '>', 0)
            ->select('sales_channel_id', 'amount')
            ->get();
    
        $marketplaceNames = SalesChannel::pluck('name', 'id');
        $marketplaceSpendText = $marketplaceSpends->map(function ($spend) use ($marketplaceNames) {
            $channelName = $marketplaceNames->get($spend->sales_channel_id);
            $formattedAmount = number_format($spend->amount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedAmount}";
        })->implode("\n");
    
        $totalSpend = $marketplaceSpends->sum('amount') + $socialMediaSpends->sum('amount');
        $formattedTotalSpend = number_format($totalSpend, 0, ',', '.');

        $message = <<<EOD
        🔥Laporan Transaksi CLEORA🔥
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

        📊 Pengeluaran Iklan Social Media Kemarin
        {$socialMediaSpendText}

        📊 Pengeluaran Iklan Marketplace Kemarin
        {$marketplaceSpendText}

        Total Ads Spent: Rp {$formattedTotalSpend}
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

        $salesChannelProjection = $thisMonthSalesChannelData->map(function ($item) use ($salesChannelNames, $daysPassed, $remainingDays) {
            $channelName = $salesChannelNames->get($item->sales_channel_id);
            $dailyAverage = $daysPassed > 0 ? $item->total_amount / $daysPassed : 0;
            $projectedAmount = $item->total_amount + ($dailyAverage * $remainingDays);
            $formattedProjectedAmount = number_format($projectedAmount, 0, ',', '.');
            return "{$channelName}: Rp {$formattedProjectedAmount}";
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

    public function importFromGoogleSheet()
    {
        $range = 'Ads Summary!A2:H'; // Adjusted to match the columns being processed
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            // Parse the date from row[0] and filter to process only current month data
            $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
            if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                continue;
            }
            $salesChannelData = [
                4 => $row[1] ?? null, // Tiktok Shop (sales_channel_id == 4)
                1 => $row[3] ?? null, // Shopee (sales_channel_id == 1)
                3 => $row[4] ?? null, // Tokopedia (sales_channel_id == 3)
                2 => $row[5] ?? null, // Lazada (sales_channel_id == 2)
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
                1 => $row[2] ?? null, // Facebook (social_media_id == 1)
                2 => $row[6] ?? null, // Snack Video (social_media_id == 2)
                5 => $row[7] ?? null, // Google Ads (social_media_id == 5)
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

    public function importVisitCleora()
    {
        $range = '[Cleora] Visit, Sales, Transaction!A3:E'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
            if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                continue;
            }
            $salesChannelData = [
                1 => $row[1] ?? null,
                4 => $row[2] ?? null,
                2 => $row[3] ?? null, 
                3 => $row[4] ?? null, 
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
        $range = '[Azrina] Visit, Sales, Transaction!A3:E'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $currentMonth = Carbon::now()->format('Y-m');

        foreach ($sheetData as $row) {
            $date = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d');
            if (Carbon::parse($date)->format('Y-m') !== $currentMonth) {
                continue;
            }
            $salesChannelData = [
                1 => $row[1] ?? null,
                4 => $row[2] ?? null,
                2 => $row[3] ?? null, 
                3 => $row[4] ?? null, 
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
}