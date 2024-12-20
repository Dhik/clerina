<?php

namespace App\Domain\Product\Controllers;

use App\Http\Controllers\Controller;
use App\Domain\Product\BLL\Product\ProductBLLInterface;
use App\Domain\Product\Models\Product;
use App\Domain\Talent\Models\TalentContent;
use App\Domain\Order\Models\Order;
use App\Domain\Product\Requests\ProductRequest;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use App\Domain\Product\Import\ProductImport;
use App\Domain\Sales\Enums\SalesChannelEnum;
use App\Domain\Sales\Models\SalesChannel;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Auth;

/**
 * @property ProductBLLInterface productBLL
 */
class ProductController extends Controller
{
    public function __construct(ProductBLLInterface $productBLL)
    {
        $this->productBLL = $productBLL;
    }

    /**
     * Display a listing of the resource.
     *
     */
    public function index()
    {
        return view('admin.product.index');
    }

    public function data(Request $request)
    {
        // Get the selected month from the request, default to current month if not provided
        $selectedMonth = $request->input('month', date('Y-m'));

        $products = Product::where('tenant_id', Auth::user()->current_tenant_id)->get();

        // Filter orders by the selected month and current tenant
        $orderCounts = Order::where('tenant_id', Auth::user()->current_tenant_id)
            ->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($selectedMonth)), 
                date('m', strtotime($selectedMonth))
            ])
            ->selectRaw('sku, COUNT(*) as count')
            ->groupBy('sku')
            ->pluck('count', 'sku');

        return DataTables::of($products)
            ->addColumn('action', function ($product) {
                return '
                    <button class="btn btn-sm btn-primary viewButton" 
                        data-id="' . $product->id . '" 
                        data-toggle="modal" 
                        data-target="#viewProductModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editButton" 
                        data-id="' . $product->id . '" 
                        data-product="' . htmlspecialchars($product->product, ENT_QUOTES, 'UTF-8') . '" 
                        data-stock="' . $product->stock . '" 
                        data-sku="' . $product->sku . '" 
                        data-harga_jual="' . $product->harga_jual . '" 
                        data-harga_markup="' . $product->harga_markup . '" 
                        data-harga_cogs="' . $product->harga_cogs . '" 
                        data-harga_batas_bawah="' . $product->harga_batas_bawah . '" 
                        data-tenant_id="' . $product->tenant_id . '" 
                        data-toggle="modal" 
                        data-target="#productModal">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteButton" data-id="' . $product->id . '"><i class="fas fa-trash-alt"></i></button>
                ';
            })
            ->addColumn('order_count', function ($product) use ($orderCounts) {
                return $orderCounts->filter(function($count, $sku) use ($product) {
                    return strpos($sku, $product->sku) !== false;
                })->sum() ?? 0;
            })
            ->rawColumns(['action'])
            ->make(true);
    }


    /**
     * Show the form for creating a new resource.
     *
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductRequest $request
     */
    public function store(ProductRequest $request)
    {
        try {
            $product = new Product();
            $product->product = $request->product;
            $product->stock = $request->stock;
            $product->sku = $request->sku;
            $product->harga_jual = $request->harga_jual;
            $product->harga_markup = $request->harga_markup;
            $product->harga_cogs = $request->harga_cogs;
            $product->harga_batas_bawah = $request->harga_batas_bawah;
            $product->tenant_id = Auth::user()->current_tenant_id;
            $product->save();

            return response()->json(['message' => 'Product added successfully'], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to add product'], 500);
        }
    }

    public function getOrders(Product $product, Request $request)
    {
        $ordersQuery = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->orderBy('date', 'desc');

        // Apply filter if sales_channel is provided
        if (request('sales_channel')) {
            $ordersQuery->where('sales_channel_id', request('sales_channel'));
        }
        if (request('month')) {
            $selectedMonth = $request->input('month', date('Y-m'));
            $ordersQuery->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($selectedMonth)), 
                date('m', strtotime($selectedMonth))
            ]);
        }
        // Return DataTable response
        return datatables()->of($ordersQuery)
            ->addColumn('total_price', function ($order) {
                return number_format($order->amount, 0, ',', '.');
            })
            ->addColumn('date', function ($order) {
                return \Carbon\Carbon::parse($order->date)->format('Y-m-d');
            })
            ->rawColumns(['total_price'])
            ->make(true);
    }


    public function getOrderCountBySku(Product $product, Request $request)
    {
        $salesChannelId = request('sales_channel');
        $month = $request->input('month', date('Y-m'));

        $orders = Order::where('sku', 'LIKE', '%' . $product->sku . '%');
        if ($salesChannelId) {
            $orders->where('sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $orders->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ]);
        }

        $orders = $orders->select('sku', DB::raw('count(*) as count'))
                        ->groupBy('sku')
                        ->get();

        $skuData = $orders->map(function ($order) {
            return [
                'sku' => $order->sku,
                'count' => $order->count
            ];
        });

        return response()->json($skuData);
    }


    public function getOrderCountBySalesChannel($productId, Request $request)
    {
        $product = Product::findOrFail($productId);
        $salesChannelId = request('sales_channel');
        $month = $request->input('month', date('Y-m'));

        $orderCounts = Order::where('sku', 'LIKE', '%' . $product->sku . '%');

        // Apply sales_channel filter if provided
        if ($salesChannelId) {
            $orderCounts->where('sales_channel_id', $salesChannelId);
        }

        if ($month) {
            $orderCounts->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ]);
        }    

        $orderCounts = $orderCounts->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
                                ->select('sales_channels.name as sales_channel', DB::raw('COUNT(orders.id) as order_count'))
                                ->groupBy('sales_channels.id', 'sales_channels.name')
                                ->get();

        $labels = $orderCounts->pluck('sales_channel');
        $data = $orderCounts->pluck('order_count');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }


    public function getOrderCountPerDay(Product $product, Request $request)
    {
        $type = request('type', 'daily');
        $salesChannel = request('sales_channel');
        $month = $request->input('month', date('Y-m'));

        $query = Order::where('sku', 'LIKE', '%' . $product->sku . '%');

        if (!is_null($salesChannel)) {
            $query->where('sales_channel_id', $salesChannel);
        }
        if ($month) {
            $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ]);
        }    
        $orderCounts = $query->selectRaw('DATE(date) as period, COUNT(id_order) as order_count')
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get();

        $labels = $orderCounts->pluck('period');
        $data = $orderCounts->pluck('order_count');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }


    public function getTalentContent($productId, Request $request)
    {
        $product = Product::findOrFail($productId);
        $month = $request->input('month');
        
        $talentContent = TalentContent::where('sku', $product->sku)
            ->select('talent_content.*', 'product as product_name'); 
        
        if ($month) {
            $talentContent->whereRaw('YEAR(posting_date) = ? AND MONTH(posting_date) = ?', [
                date('Y', strtotime($month)), 
                date('m', strtotime($month))
            ]);
        }

        return DataTables::of($talentContent)
            ->addColumn('status', function($row) {
                return $row->done ? 'Completed' : 'Pending';
            })
            ->rawColumns(['status'])
            ->make(true);
    }

    public function getSalesChannels(): mixed
    {
        return Cache::rememberForever(SalesChannelEnum::AllSalesChannelCacheTag, function () {
            return SalesChannel::orderBy('name')->get();
        });
    }

    /**
     * Show the details of the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $salesChannels = $this->getSalesChannels();
        return view('admin.product.show', compact('product', 'salesChannels'));
    }

    public function getSalesMetrics(Product $product, Request $request)
    {
        $salesChannelId = request('sales_channel');
        $month = $request->input('month');

        $baseQuery = Order::where('sku', 'LIKE', '%' . $product->sku . '%');
        if ($salesChannelId) {
            $baseQuery->where('sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $baseQuery->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($month)), 
                date('m', strtotime($month))
            ]);
        }

        $uniqueCustomerCount = (clone $baseQuery)
            ->where('customer_name', 'NOT LIKE', '%*%')
            ->where('customer_phone_number', 'NOT LIKE', '%*%')
            ->select('customer_name', 'customer_phone_number')
            ->distinct()
            ->count();

        $totalOrdersCount = (clone $baseQuery)->count();
        $totalAmountSum = (clone $baseQuery)->sum('amount');
        $ordersWithoutCommas = (clone $baseQuery)->where('sku', 'NOT LIKE', '%,%');
        $ordersPerCustomerRatio = $uniqueCustomerCount > 0 ? $totalOrdersCount / $uniqueCustomerCount : 0;

        $averageOrderValue = $totalOrdersCount > 0 ? $totalAmountSum / $totalOrdersCount : 0;
        $avgDailyOrdersCount = (clone $ordersWithoutCommas)
            ->selectRaw('COUNT(id) / COUNT(DISTINCT DATE(date)) as avg_daily_orders')
            ->value('avg_daily_orders');

        $netProfit = (clone $ordersWithoutCommas)
            ->selectRaw('SUM(amount - ?) as net_profit', [$product->harga_cogs])
            ->value('net_profit') ?? 0;

        return response()->json([
            'uniqueCustomerCount' => $uniqueCustomerCount,
            'totalOrdersCount' => $totalOrdersCount,
            'totalAmountSum' => $totalAmountSum,
            'avgDailyOrdersCount' => round($avgDailyOrdersCount, 2),
            'ordersPerCustomerRatio' => round($ordersPerCustomerRatio, 2),
            'averageOrderValue' => round($averageOrderValue, 2),
            'netProfitSingleProduct' => $netProfit,
        ]);
    }



    public function getMarketingMetrics(Product $product)
    {
        // Use aggregate queries for counts to reduce memory usage
        $talentContentCount = TalentContent::where('sku', $product->sku)->count();
        
        $uniqueTalentIdCount = TalentContent::where('sku', $product->sku)
            ->distinct('talent_id')
            ->count('talent_id');

        // Use aggregate query to calculate total views, likes, and comments directly in the database
        $engagementData = TalentContent::where('talent_content.sku', $product->sku)
            ->join('campaign_contents', 'talent_content.upload_link', '=', 'campaign_contents.link')
            ->join('statistics', 'campaign_contents.id', '=', 'statistics.campaign_content_id')
            ->selectRaw('SUM(statistics.view) as total_views, SUM(statistics.like) as total_likes, SUM(statistics.comment) as total_comments')
            ->first();

        // Prevent division by zero and calculate average engagement rate
        $totalViews = $engagementData->total_views ?? 0;
        $totalLikes = $engagementData->total_likes ?? 0;
        $totalComments = $engagementData->total_comments ?? 0;

        $averageEngagementRate = $totalViews > 0
            ? round(($totalLikes + $totalComments) / $totalViews * 100, 2)
            : 0;

        return response()->json([
            'talentContentCount' => $talentContentCount,
            'uniqueTalentIdCount' => $uniqueTalentIdCount,
            'averageEngagementRate' => $averageEngagementRate,
        ]);
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function edit(Product $product)
    {
        // Return product data in JSON format for the AJAX call
        return response()->json([
            'product' => $product,
        ]);
    }

    /**
     * Update the specified product in storage.
     *
     * @param ProductRequest $request
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function update(ProductRequest $request, Product $product)
    {
        try {
            // Validate and update the product data
            $validatedData = $request->validated();
            $product->update($validatedData);
    
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully!',
                'product' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product from storage.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function destroy(Product $product)
    {
        try {
            // Delete the product from the database
            $product->delete();

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully!'
            ]);
        } catch (\Exception $e) {
            // Handle any errors
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    public function topProduct(Request $request)
    {
        $dataResponse = $this->data($request);
        $productsData = json_decode($dataResponse->getContent(), true)['data'];

        $topProduct = null;
        $maxOrderCount = 0;

        foreach ($productsData as $product) {
            if ($product['order_count'] > $maxOrderCount) {
                $maxOrderCount = $product['order_count'];
                $topProduct = $product;
            }
        }

        if ($topProduct) {
            return response()->json([
                'product' => $topProduct['product'],
                'sku' => $topProduct['sku'], 
                'order_count' => $topProduct['order_count'],
            ]);
        }
        return response()->json(['message' => 'No top product found']);
    }

}
