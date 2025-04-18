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
        
        // Get all sales channels
        $salesChannels = DB::table('sales_channels')->orderBy('id')->get();
        
        // Prepare data for DataTables
        $result = [];
        
        foreach ($dates as $date) {
            $row = [
                'date' => Carbon::parse($date)->format('Y-m-d'),
                'total_gross_revenue' => 0,
                'total_hpp' => 0,
                'total_fee_admin' => 0,
            ];
            
            // Add gross revenue for each sales channel
            foreach ($salesChannels as $channel) {
                $channelData = DB::table('laporan_keuangan')
                    ->where('tenant_id', '=', $currentTenantId)
                    ->where('date', '=', $date)
                    ->where('sales_channel_id', '=', $channel->id)
                    ->first();
                
                // Set default value to 0 if no data found
                $grossRevenue = $channelData ? ($channelData->gross_revenue ?: 0) : 0;
                $hpp = $channelData ? ($channelData->hpp ?: 0) : 0;
                $feeAdmin = $channelData ? ($channelData->fee_admin ?: 0) : 0;
                
                $row['channel_' . $channel->id] = $grossRevenue;
                $row['total_gross_revenue'] += $grossRevenue;
                $row['total_hpp'] += $hpp;
                $row['total_fee_admin'] += $feeAdmin;
            }
            
            $result[] = $row;
        }
        
        $dataTable = DataTables::of($result)
            ->editColumn('date', function ($row) {
                return $row['date'];
            })
            ->editColumn('total_gross_revenue', function ($row) {
                return '<span class="text-success">Rp ' . number_format($row['total_gross_revenue'], 0, ',', '.') . '</span>';
            })
            ->editColumn('total_hpp', function ($row) {
                return 'Rp ' . number_format($row['total_hpp'], 0, ',', '.');
            })
            ->editColumn('total_fee_admin', function ($row) {
                return 'Rp ' . number_format($row['total_fee_admin'], 0, ',', '.');
            });
        
        foreach ($salesChannels as $channel) {
            $dataTable->addColumn('channel_' . $channel->id, function ($row) use ($channel) {
                // Convert value to 0 if it's null or NaN
                $value = $row['channel_' . $channel->id] ?? 0;
                $value = is_numeric($value) ? $value : 0;
                
                return 'Rp'.number_format($value, 0, ',', '.');
            });
        }
        
        $rawColumns = ['total_gross_revenue', 'total_hpp', 'total_fee_admin'];
        
        // Add all channel columns to raw columns
        foreach ($salesChannels as $channel) {
            $rawColumns[] = 'channel_' . $channel->id;
        }
        
        return $dataTable->rawColumns($rawColumns)->make(true);
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
                SUM(lk.gross_revenue) as channel_gross_revenue
            ')
            ->groupBy('sc.id', 'sc.name')
            ->orderBy('sc.name')
            ->get();
        
        return response()->json([
            'total_gross_revenue' => $summary->total_gross_revenue ?? 0,
            'total_hpp' => $summary->total_hpp ?? 0,
            'total_fee_admin' => $summary->total_fee_admin ?? 0,
            'channel_summary' => $channelSummary
        ]);
    }
    
    public function refresh()
    {
        // Implement your data refresh logic here
        // This is just a placeholder method
        
        return response()->json([
            'success' => true,
            'message' => 'Data refreshed successfully'
        ]);
    }
}