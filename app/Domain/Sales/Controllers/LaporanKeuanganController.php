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

        return view('admin.laporan_keuangan.index', compact('salesChannels', 'socialMedia'));
    }
    
    public function get(Request $request)
    {
        $currentTenantId = Auth::user()->current_tenant_id;
        $type = $request->input('type', 'summary');
        
        $startDate = null;
        $endDate = null;
        
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }
        
        // Handle different tab types
        if ($type === 'summary') {
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
        } else {
            // For pivot tables (gross_revenue, hpp, fee_admin)
            // Get all dates in the selected range
            $datesQuery = DB::table('laporan_keuangan')
                ->select('date')
                ->where('tenant_id', '=', $currentTenantId)
                ->distinct();
            
            // Apply date filtering to dates
            if (!is_null($request->input('filterDates'))) {
                $datesQuery->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate);
            } else {
                $datesQuery->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
            }
            
            $dates = $datesQuery->orderBy('date')->pluck('date');
            
            // Get all sales channels
            $salesChannels = DB::table('sales_channels')
                ->orderBy('name')
                ->get();
            
            $query = DB::table('laporan_keuangan')
                ->where('tenant_id', '=', $currentTenantId);
                
            if (!is_null($request->input('filterDates'))) {
                $query->where('date', '>=', $startDate)
                    ->where('date', '<=', $endDate);
            } else {
                $query->whereMonth('date', Carbon::now()->month)
                    ->whereYear('date', Carbon::now()->year);
            }
            
            $allData = $query->get();

            // Prepare pivot data
            $result = [];
            
            foreach ($dates as $date) {
                $formattedDate = Carbon::parse($date)->format('Y-m-d');
                $row = ['date' => $formattedDate];
                $totalValue = 0;
                
                // Initialize all channel values to 0
                foreach ($salesChannels as $channel) {
                    $row['channel_' . $channel->id] = 0;
                }
                
                // Find data for this date
                $dateData = $allData->where('date', $date);
                
                // Fill in values for each channel
                foreach ($dateData as $data) {
                    $value = 0;
                    
                    if ($type === 'gross_revenue') {
                        $value = $data->gross_revenue ?: 0;
                    } else if ($type === 'hpp') {
                        $value = $data->hpp ?: 0;
                    } else if ($type === 'fee_admin') {
                        $value = $data->fee_admin ?: 0;
                    }
                    
                    $row['channel_' . $data->sales_channel_id] = $value;
                    $totalValue += $value;
                }
                
                $row['total'] = $totalValue;
                $result[] = $row;
            }
            
            $dataTable = DataTables::of($result)
                ->editColumn('date', function ($row) {
                    return $row['date'];
                });
            
            // Add columns for each sales channel
            foreach ($salesChannels as $channel) {
                $dataTable->editColumn('channel_' . $channel->id, function ($row) use ($channel) {
                    $value = $row['channel_' . $channel->id];
                    return 'Rp ' . number_format($value, 0, ',', '.');
                });
            }
            
            // Add total column
            $dataTable->editColumn('total', function ($row) {
                return 'Rp ' . number_format($row['total'], 0, ',', '.');
            });
            
            $rawColumns = ['date'];
            
            // Add all channel columns to rawColumns
            foreach ($salesChannels as $channel) {
                $rawColumns[] = 'channel_' . $channel->id;
            }
            $rawColumns[] = 'total';
            
            return $dataTable->rawColumns($rawColumns)->make(true);
        }
    }
    
    public function getDetails(Request $request)
    {
        $date = $request->input('date');
        $type = $request->input('type');
        $currentTenantId = Auth::user()->current_tenant_id;
        
        if ($type === 'hpp') {
            $salesChannels = DB::table('sales_channels')
                ->orderBy('name')
                ->get();
                
            $allSkuData = [];
            $channelSummaries = [];
            $grandTotal = 0;
            
            // For each sales channel, get SKU-based HPP details
            foreach ($salesChannels as $channel) {
                $orders = DB::table('orders')
                    ->where('tenant_id', $currentTenantId)
                    ->where('success_date', $date)
                    ->where('sales_channel_id', $channel->id)
                    ->get();
                    
                $skuData = [];
                $channelTotal = 0;
                
                // Process each order
                foreach ($orders as $order) {
                    // Find product information
                    $product = DB::table('products')
                        ->where('tenant_id', $currentTenantId)
                        ->where('sku', $order->sku)
                        ->first();
                        
                    if ($product) {
                        $unitPrice = $product->harga_satuan ?: 0;
                        $totalPrice = $unitPrice * $order->qty;
                        $channelTotal += $totalPrice;
                        $grandTotal += $totalPrice;
                        
                        // Check if SKU already exists in the array
                        $skuExists = false;
                        foreach ($skuData as &$item) {
                            if ($item['sku'] === $order->sku) {
                                $item['qty'] += $order->qty;
                                $item['total'] += $totalPrice;
                                $skuExists = true;
                                break;
                            }
                        }
                        
                        // If SKU doesn't exist, add it
                        if (!$skuExists) {
                            $skuData[] = [
                                'sku' => $order->sku,
                                'product' => $product->product,
                                'qty' => $order->qty,
                                'hpp' => $unitPrice,
                                'total' => $totalPrice
                            ];
                        }
                    }
                }
                
                // Sort by total price (highest first)
                usort($skuData, function($a, $b) {
                    return $b['total'] <=> $a['total'];
                });
                
                $allSkuData[$channel->id] = $skuData;
                $channelSummaries[$channel->id] = [
                    'name' => $channel->name,
                    'total' => $channelTotal
                ];
            }
            
            return response()->json([
                'date' => $date,
                'type' => $type,
                'channels' => $salesChannels,
                'data' => $allSkuData,
                'summaries' => $channelSummaries,
                'grand_total' => $grandTotal
            ]);
        } else {
            // For other types, use the existing query
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
    }

    public function getGrossRevenueDetails(Request $request)
    {
        $date = $request->input('date');
        $type = $request->input('type');
        $currentTenantId = Auth::user()->current_tenant_id;
        
        if ($type === 'gross_revenue') {
            $salesChannels = DB::table('sales_channels')
                ->orderBy('name')
                ->get();
                
            $allSkuData = [];
            $channelSummaries = [];
            $grandTotal = 0;
            
            // For each sales channel, get SKU-based gross revenue details
            foreach ($salesChannels as $channel) {
                $orders = DB::table('orders')
                    ->where('tenant_id', $currentTenantId)
                    ->where('success_date', $date)
                    ->where('sales_channel_id', $channel->id)
                    ->get();
                    
                $skuData = [];
                $channelTotal = 0;
                
                // Process each order
                foreach ($orders as $order) {
                    // Find product information
                    $product = DB::table('products')
                        ->where('tenant_id', $currentTenantId)
                        ->where('sku', $order->sku)
                        ->first();
                        
                    if ($product) {
                        $unitPrice = $product->harga_jual ?: 0; // Using harga_jual instead of harga_satuan
                        $totalPrice = $unitPrice * $order->qty;
                        $channelTotal += $totalPrice;
                        $grandTotal += $totalPrice;
                        
                        // Check if SKU already exists in the array
                        $skuExists = false;
                        foreach ($skuData as &$item) {
                            if ($item['sku'] === $order->sku) {
                                $item['qty'] += $order->qty;
                                $item['total'] += $totalPrice;
                                $skuExists = true;
                                break;
                            }
                        }
                        
                        // If SKU doesn't exist, add it
                        if (!$skuExists) {
                            $skuData[] = [
                                'sku' => $order->sku,
                                'product' => $product->product,
                                'qty' => $order->qty,
                                'gross_revenue' => $unitPrice, // Renamed from 'hpp' to 'gross_revenue'
                                'total' => $totalPrice
                            ];
                        }
                    }
                }
                
                // Sort by total price (highest first)
                usort($skuData, function($a, $b) {
                    return $b['total'] <=> $a['total'];
                });
                
                $allSkuData[$channel->id] = $skuData;
                $channelSummaries[$channel->id] = [
                    'name' => $channel->name,
                    'total' => $channelTotal
                ];
            }
            
            return response()->json([
                'date' => $date,
                'type' => $type,
                'channels' => $salesChannels,
                'data' => $allSkuData,
                'summaries' => $channelSummaries,
                'grand_total' => $grandTotal
            ]);
        } else {
            // For other types, use the existing query (same as in getDetails)
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
        
        return response()->json([
            'total_gross_revenue' => $summary->total_gross_revenue ?? 0,
            'total_hpp' => $summary->total_hpp ?? 0,
            'total_fee_admin' => $summary->total_fee_admin ?? 0,
            'net_profit' => $netProfit,
            'hpp_percentage' => $hppPercentage,
            'channel_summary' => $channelSummary
        ]);
    }
}