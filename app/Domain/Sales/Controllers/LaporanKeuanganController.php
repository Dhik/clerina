<?php

namespace App\Domain\Sales\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;
use App\Domain\Sales\Models\LaporanKeuangan;
use Carbon\Carbon;
use Auth;
use App\Domain\Order\Models\Order;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Sales\Models\AdSpentSocialMedia;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Services\GoogleSheetService;

use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use App\Domain\Sales\Services\TelegramService;
use Illuminate\Support\Facades\Http;

use App\Domain\Marketing\BLL\SocialMedia\SocialMediaBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;

class LaporanKeuanganController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        GoogleSheetService $googleSheetService,
        protected SalesChannelBLLInterface $salesChannelBLL,
        protected SocialMediaBLLInterface $socialMediaBLL,
    ) {
        $this->googleSheetService = $googleSheetService;
    }
    
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnySales', Sales::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();
        $socialMedia = $this->socialMediaBLL->getSocialMedia();

        return view('admin.sales.lk_index', compact('salesChannels', 'socialMedia'));
    }
    
    public function get(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        
        $startDate = null;
        $endDate = null;
        
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }
        
        // Get all distinct dates in the selected range
        $query = DB::table('laporan_keuangan')
            ->select('date')
            ->where('tenant_id', '=', $currentTenantId)
            ->distinct();
        
        // Apply date filtering
        if (!is_null($request->input('filterDates'))) {
            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        } else {
            $query->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year);
        }
        
        $dates = $query->orderBy('date')->pluck('date');
        
        // Prepare data for DataTables
        $result = [];
        
        foreach ($dates as $date) {
            // Get summary data for this date
            $summaryData = DB::table('laporan_keuangan')
                ->where('tenant_id', '=', $currentTenantId)
                ->where('date', '=', $date)
                ->selectRaw('
                    SUM(gross_revenue) as total_gross_revenue,
                    SUM(hpp) as total_hpp,
                    SUM(fee_admin) as total_fee_admin
                ')
                ->first();
            
            // Calculate net profit
            $netProfit = $summaryData->total_gross_revenue - $summaryData->total_fee_admin;
            
            $row = [
                'date' => Carbon::parse($date)->format('Y-m-d'),
                'total_gross_revenue' => $summaryData->total_gross_revenue ?: 0,
                'total_hpp' => $summaryData->total_hpp ?: 0,
                'total_fee_admin' => $summaryData->total_fee_admin ?: 0,
                'net_profit' => $netProfit ?: 0,
            ];
            
            $result[] = $row;
        }
        
        $dataTable = DataTables::of($result)
            ->editColumn('date', function ($row) {
                return $row['date'];
            })
            ->editColumn('total_gross_revenue', function ($row) {
                return '<a href="#" class="text-success show-details" data-type="gross_revenue" data-date="' . $row['date'] . '">Rp ' . number_format($row['total_gross_revenue'], 0, ',', '.') . '</a>';
            })
            ->editColumn('total_hpp', function ($row) {
                return '<a href="#" class="show-details" data-type="hpp" data-date="' . $row['date'] . '">Rp ' . number_format($row['total_hpp'], 0, ',', '.') . '</a>';
            })
            ->editColumn('total_fee_admin', function ($row) {
                return '<a href="#" class="show-details" data-type="fee_admin" data-date="' . $row['date'] . '">Rp ' . number_format($row['total_fee_admin'], 0, ',', '.') . '</a>';
            })
            ->editColumn('net_profit', function ($row) {
                return '<a href="#" class="text-primary show-details" data-type="net_profit" data-date="' . $row['date'] . '">Rp ' . number_format($row['net_profit'], 0, ',', '.') . '</a>';
            });
        
        return $dataTable->rawColumns(['total_gross_revenue', 'total_hpp', 'total_fee_admin', 'net_profit'])->make(true);
    }
    
    public function getDetails(Request $request)
    {
        $date = $request->input('date');
        $type = $request->input('type');
        $currentTenantId = Auth::user()->current_tenant_id;
        
        $query = DB::table('laporan_keuangan as lk')
            ->join('sales_channels as sc', 'lk.sales_channel_id', '=', 'sc.id')
            ->where('lk.tenant_id', '=', $currentTenantId)
            ->where('lk.date', '=', $date)
            ->select('sc.name as channel_name', 'lk.*');
            
        $data = $query->get();
        
        // Transform data based on the requested type
        $details = [];
        $totalGrossRevenue = 0;
        $totalHpp = 0;
        $totalFeeAdmin = 0;
        $totalNetProfit = 0;
        
        foreach ($data as $row) {
            $grossRevenue = $row->gross_revenue ?: 0;
            $hpp = $row->hpp ?: 0;
            $feeAdmin = $row->fee_admin ?: 0;
            $netProfit = $grossRevenue - $feeAdmin;
            $hppPercentage = $grossRevenue > 0 ? ($hpp / $grossRevenue) * 100 : 0;
            
            $totalGrossRevenue += $grossRevenue;
            $totalHpp += $hpp;
            $totalFeeAdmin += $feeAdmin;
            $totalNetProfit += $netProfit;
            
            $details[] = [
                'channel_name' => $row->channel_name,
                'gross_revenue' => $grossRevenue,
                'hpp' => $hpp,
                'fee_admin' => $feeAdmin,
                'net_profit' => $netProfit,
                'hpp_percentage' => $hppPercentage
            ];
        }
        
        // Calculate total percentage
        $totalHppPercentage = $totalGrossRevenue > 0 ? ($totalHpp / $totalGrossRevenue) * 100 : 0;
        
        // Add totals
        $summary = [
            'total_gross_revenue' => $totalGrossRevenue,
            'total_hpp' => $totalHpp,
            'total_fee_admin' => $totalFeeAdmin,
            'total_net_profit' => $totalNetProfit,
            'total_hpp_percentage' => $totalHppPercentage
        ];
        
        return response()->json([
            'date' => $date,
            'type' => $type,
            'details' => $details,
            'summary' => $summary
        ]);
    }
    
    public function getSummary(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        
        $startDate = null;
        $endDate = null;
        
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }
        
        $query = DB::table('laporan_keuangan')
            ->where('tenant_id', '=', $currentTenantId);
        
        // Apply date filtering
        if (!is_null($request->input('filterDates'))) {
            $query->where('date', '>=', $startDate)
                  ->where('date', '<=', $endDate);
        } else {
            $query->whereMonth('date', Carbon::now()->month)
                  ->whereYear('date', Carbon::now()->year);
        }
        
        $summary = $query->selectRaw('
            SUM(gross_revenue) as total_gross_revenue,
            SUM(hpp) as total_hpp,
            SUM(fee_admin) as total_fee_admin
        ')->first();
        
        // Calculate net profit
        $netProfit = ($summary->total_gross_revenue ?? 0) - ($summary->total_fee_admin ?? 0);
        
        // Calculate HPP percentage
        $hppPercentage = ($summary->total_gross_revenue > 0) 
            ? (($summary->total_hpp / $summary->total_gross_revenue) * 100) 
            : 0;
            
        // Get channel-wise summary
        $channelSummary = DB::table('laporan_keuangan as lk')
            ->join('sales_channels as sc', 'lk.sales_channel_id', '=', 'sc.id')
            ->where('lk.tenant_id', '=', $currentTenantId);
            
        // Apply date filtering to channel summary
        if (!is_null($request->input('filterDates'))) {
            $channelSummary->where('lk.date', '>=', $startDate)
                          ->where('lk.date', '<=', $endDate);
        } else {
            $channelSummary->whereMonth('lk.date', Carbon::now()->month)
                          ->whereYear('lk.date', Carbon::now()->year);
        }
        
        $channelSummary = $channelSummary->selectRaw('
                sc.id as channel_id,
                sc.name as channel_name,
                SUM(lk.gross_revenue) as channel_gross_revenue,
                SUM(lk.hpp) as channel_hpp,
                SUM(lk.fee_admin) as channel_fee_admin
            ')
            ->groupBy('sc.id', 'sc.name')
            ->orderBy('channel_gross_revenue', 'desc')
            ->get();
            
        // Calculate net profit and HPP percentage per channel
        foreach ($channelSummary as $channel) {
            $channel->channel_net_profit = $channel->channel_gross_revenue - $channel->channel_fee_admin;
            $channel->channel_hpp_percentage = ($channel->channel_gross_revenue > 0) 
                ? (($channel->channel_hpp / $channel->channel_gross_revenue) * 100) 
                : 0;
        }
        
        // Get daily trend data
        $dailyTrend = DB::table('laporan_keuangan')
            ->where('tenant_id', '=', $currentTenantId);
            
        // Apply date filtering to daily trend
        if (!is_null($request->input('filterDates'))) {
            $dailyTrend->where('date', '>=', $startDate)
                      ->where('date', '<=', $endDate);
        } else {
            $dailyTrend->whereMonth('date', Carbon::now()->month)
                      ->whereYear('date', Carbon::now()->year);
        }
        
        $dailyTrend = $dailyTrend->selectRaw('
                date,
                SUM(gross_revenue) as daily_gross_revenue,
                SUM(hpp) as daily_hpp,
                SUM(fee_admin) as daily_fee_admin
            ')
            ->groupBy('date')
            ->orderBy('date')
            ->get();
            
        // Calculate net profit for daily trend
        foreach ($dailyTrend as $day) {
            $day->daily_net_profit = $day->daily_gross_revenue - $day->daily_fee_admin;
            $day->date_formatted = Carbon::parse($day->date)->format('d M');
        }
        
        return response()->json([
            'total_gross_revenue' => $summary->total_gross_revenue ?? 0,
            'total_hpp' => $summary->total_hpp ?? 0,
            'total_fee_admin' => $summary->total_fee_admin ?? 0,
            'net_profit' => $netProfit,
            'hpp_percentage' => $hppPercentage,
            'channel_summary' => $channelSummary,
            'daily_trend' => $dailyTrend
        ]);
    }
}