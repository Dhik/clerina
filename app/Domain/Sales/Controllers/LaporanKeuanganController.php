<?php

namespace App\Domain\Sales\Controllers;

use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
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
        
        $baseQuery = DB::table('laporan_keuangan as lk')
            ->select(
                'lk.gross_revenue',
                'lk.hpp',
                'lk.date',
                'lk.fee_admin'
            )
            ->where('lk.tenant_id', '=', $currentTenantId);
        
        // Apply date filtering
        if (!is_null($request->input('filterDates'))) {
            $baseQuery->where('lk.date', '>=', $startDate)
                    ->where('lk.date', '<=', $endDate);
        } else {
            $baseQuery->whereMonth('lk.date', Carbon::now()->month)
                    ->whereYear('lk.date', Carbon::now()->year);
        }
        
        $baseQuery->orderBy('lk.date');
        $data = $baseQuery->get();
        
        return DataTables::of($data)
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d');
            })
            ->editColumn('gross_revenue', function ($row) {
                return $row->gross_revenue ?? 0;
            })
            ->editColumn('hpp', function ($row) {
                return $row->hpp ?? 0;
            })
            ->editColumn('fee_admin', function ($row) {
                return $row->fee_admin ?? 0;
            })
            ->make(true);
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
        
        return response()->json([
            'total_gross_revenue' => $summary->total_gross_revenue ?? 0,
            'total_hpp' => $summary->total_hpp ?? 0,
            'total_fee_admin' => $summary->total_fee_admin ?? 0
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