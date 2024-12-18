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

    public function getOrders(Product $product)
    {
        $orders = Order::where('sku', 'LIKE', '%'.$product->sku.'%')
                    ->orderBy('date', 'desc'); 

        return datatables()->of($orders)
            ->addColumn('total_price', function($order) {
                return number_format($order->amount, 0, ',', '.');
            })
            ->addColumn('date', function($order) {
                return \Carbon\Carbon::parse($order->date)->format('Y-m-d');
            })
            ->rawColumns(['total_price'])
            ->make(true);
    }

    public function getOrderCountBySku(Product $product)
    {
        $orders = Order::where('sku', 'LIKE', '%'.$product->sku.'%')
                        ->select('sku', DB::raw('count(*) as count'))
                        ->groupBy('sku')
                        ->get();

        $skuData = $orders->map(function($order) {
            return [
                'sku' => $order->sku,
                'count' => $order->count
            ];
        });

        return response()->json($skuData);
    }

    public function getOrderCountBySalesChannel($productId)
    {
        // Find the product by ID
        $product = Product::findOrFail($productId);

        // Retrieve orders for the specific product SKU and count orders per sales channel
        $orderCounts = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
            ->select('sales_channels.name as sales_channel', DB::raw('COUNT(orders.id) as order_count'))
            ->groupBy('sales_channels.id', 'sales_channels.name')  // Add sales_channels.name here
            ->get();

        // Prepare data for the bar chart
        $labels = $orderCounts->pluck('sales_channel');
        $data = $orderCounts->pluck('order_count');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }

    public function getOrderCountPerDay(Product $product)
    {
        $type = request('type', 'daily'); // Default to daily if no type is specified

        if ($type === 'daily') {
            $orderCounts = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
                ->selectRaw('DATE(date) as order_date, COUNT(id_order) as order_count')
                ->groupBy('order_date')
                ->orderBy('order_date', 'asc')
                ->get();

            $labels = $orderCounts->pluck('order_date');
        } else {
            $orderCounts = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
                ->selectRaw('DATE_FORMAT(date, "%Y-%m") as order_month, COUNT(id_order) as order_count')
                ->groupBy('order_month')
                ->orderBy('order_month', 'asc')
                ->get();

            $labels = $orderCounts->pluck('order_month');
        }

        $data = $orderCounts->pluck('order_count');

        return response()->json([
            'labels' => $labels,
            'data' => $data
        ]);
    }



    public function getTalentContent($productId)
    {
        $product = Product::findOrFail($productId);
        
        $talentContent = TalentContent::where('sku', $product->sku)
            ->select('talent_content.*', 'product as product_name'); // Use the 'product' column directly

        return DataTables::of($talentContent)
            ->addColumn('actions', function($row) use ($productId) {
                return '
                    <button class="btn btn-sm btn-primary viewTalentContentButton" 
                        data-id="' . $row->id . '" 
                        data-product-id="' . $productId . '"
                        data-toggle="modal" 
                        data-target="#viewTalentContentModal">
                        <i class="fas fa-eye"></i>
                    </button>
                    <button class="btn btn-sm btn-success editTalentContentButton" 
                        data-id="' . $row->id . '"
                        data-product-id="' . $productId . '">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-sm btn-danger deleteTalentContentButton" 
                        data-id="' . $row->id . '"
                        data-product-id="' . $productId . '">
                        <i class="fas fa-trash-alt"></i>
                    </button>
                ';
            })
            ->addColumn('status', function($row) {
                return $row->done ? 'Completed' : 'Pending';
            })
            ->rawColumns(['actions', 'status'])
            ->make(true);
    }

    /**
     * Show the details of the specified product.
     *
     * @param Product $product
     * @return \Illuminate\Http\Response
     */
    public function show(Product $product)
    {
        $uniqueCustomerCount = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->where('customer_name', 'NOT LIKE', '%*%')
            ->where('customer_phone_number', 'NOT LIKE', '%*%')
            ->select('customer_name', 'customer_phone_number')
            ->distinct()
            ->count();
        
        // Get total count of orders
        $totalOrdersCount = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->count();

        $ordersPerCustomerRatio = $uniqueCustomerCount > 0 ? $totalOrdersCount / $uniqueCustomerCount : 0;

        // Get total amount sum
        $totalAmountSum = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->sum('amount');

        $averageOrderValue = $totalOrdersCount > 0 ? $totalAmountSum / $totalOrdersCount : 0;
        
        $avgDailyOrdersCount = Order::where('sku', 'LIKE', '%' . $product->sku . '%')
            ->selectRaw('COUNT(id) / COUNT(DISTINCT DATE(date)) as avg_daily_orders')
            ->value('avg_daily_orders');

        $talentContentCount = TalentContent::where('sku', $product->sku)
            ->count();
        
        $uniqueTalentIdCount = TalentContent::where('sku', $product->sku)
            ->distinct('talent_id')
            ->count('talent_id');

        $engagementData = TalentContent::where('talent_content.sku', $product->sku)
            ->join('campaign_contents', 'talent_content.upload_link', '=', 'campaign_contents.link')
            ->join('statistics', 'campaign_contents.id', '=', 'statistics.campaign_content_id')
            ->select('statistics.view', 'statistics.like', 'statistics.comment', 'campaign_contents.rate_card')
            ->get();
    
        // Calculate engagement rate and CPM for each record
        $metrics = $engagementData->map(function ($stat) {
            $views = $stat->view;
            $likes = $stat->like;
            $comments = $stat->comment;
            $rateCard = $stat->rate_card;
    
            // Calculate engagement rate
            $engagementRate = $views > 0 ? round(($likes + $comments) / $views * 100, 2) : 0;
    
            // Calculate CPM
            $cpm = $views > 0 ? round(($rateCard / $views) * 1000, 2) : 0;
    
            return [
                'engagement_rate' => $engagementRate,
                'cpm' => $cpm,
            ];
        });
    
        // Calculate averages
        $averageEngagementRate = $metrics->count() > 0
            ? round($metrics->pluck('engagement_rate')->average(), 2)
            : 0;
    
        $averageCPM = $metrics->count() > 0
            ? round($metrics->pluck('cpm')->average(), 2)
            : 0;

            return view('admin.product.show', compact(
                'product',
                'uniqueCustomerCount',
                'totalOrdersCount',
                'totalAmountSum',
                'avgDailyOrdersCount',
                'ordersPerCustomerRatio',
                'averageOrderValue',
                'talentContentCount',
                'uniqueTalentIdCount',
                'averageEngagementRate',
                'averageCPM'
            ));
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
