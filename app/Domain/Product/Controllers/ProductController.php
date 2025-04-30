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
        $selectedMonth = $request->input('month', date('Y-m'));
        $type = $request->input('type', 'Single');

        // Parse the selected month to get start and end dates
        $monthStart = date('Y-m-01', strtotime($selectedMonth));
        $monthEnd = date('Y-m-t', strtotime($selectedMonth));

        $products = Product::where('tenant_id', Auth::user()->current_tenant_id)
            ->where('type', $type)
            ->get();
            
        // Get direct order quantities for products
        $orderQuantities = Order::where('tenant_id', Auth::user()->current_tenant_id)
            ->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($selectedMonth)), 
                date('m', strtotime($selectedMonth))
            ])
            ->whereNotIn('orders.status', 
                [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
            ->selectRaw('sku, SUM(qty) as total_qty')
            ->groupBy('sku')
            ->pluck('total_qty', 'sku');
        
        // For Single products, get additional usage in bundles
        $bundleUsage = [];
        if ($type === 'Single') {
            // Get all single product SKUs
            $singleSkus = $products->pluck('sku')->toArray();
            
            // For each single product, calculate its usage in bundles
            foreach ($singleSkus as $singleSku) {
                // Query to count bundle usage for this specific single product
                $usage = \DB::table('orders as o')
                    ->join('products as p', 'o.sku', '=', 'p.sku')
                    ->where('p.type', 'Bundle')
                    ->where(function($query) use ($singleSku) {
                        $query->where('p.combination_sku_1', $singleSku)
                            ->orWhere('p.combination_sku_2', $singleSku);
                    })
                    ->whereBetween('o.date', [$monthStart, $monthEnd])
                    ->where('o.tenant_id', Auth::user()->current_tenant_id)
                    ->sum('o.qty');
                    
                $bundleUsage[$singleSku] = $usage;
            }
        }

        $dataTable = DataTables::of($products);
        
        if ($type === 'Single') {
            // For Single products, show direct orders and bundle usage
            $dataTable->addColumn('direct_orders', function($product) use ($orderQuantities) {
                return $orderQuantities[$product->sku] ?? 0;
            });
            
            $dataTable->addColumn('bundle_usage', function($product) use ($bundleUsage) {
                $usage = $bundleUsage[$product->sku] ?? 0;
                if ($usage > 0) {
                    return '<span class="text-success">' . number_format($usage) . '</span>';
                }
                return '0';
            });
            
            $dataTable->addColumn('order_count', function($product) use ($orderQuantities, $bundleUsage) {
                $direct = $orderQuantities[$product->sku] ?? 0;
                $bundle = $bundleUsage[$product->sku] ?? 0;
                $total = $direct + $bundle;
                
                return $total;
            });
        } else {
            // For Bundle products, just show order count
            $dataTable->addColumn('order_count', function ($product) use ($orderQuantities) {
                return $orderQuantities[$product->sku] ?? 0;
            });
            
            // Add combination SKUs for Bundle products
            $dataTable->addColumn('combination_skus', function($product) {
                $output = '';
                
                if ($product->combination_sku_1) {
                    $output .= '<span class="badge badge-info mr-1">' . $product->combination_sku_1 . '</span>';
                }
                
                if ($product->combination_sku_2) {
                    $output .= '<span class="badge badge-primary">' . $product->combination_sku_2 . '</span>';
                }
                
                return $output ?: '-';
            });
        }
        
        // Add action column for both tables
        $dataTable->addColumn('action', function ($product) {
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
        });
        
        // Define which columns should render HTML
        $rawColumns = ['action'];
        
        if ($type === 'Bundle') {
            $rawColumns[] = 'combination_skus';
        } else {
            $rawColumns[] = 'bundle_usage';
        }
        
        $dataTable->rawColumns($rawColumns);
        
        return $dataTable->make(true);
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
        // Get the specific product
        $productSku = $product->sku;
        
        // Base query for direct orders of this SKU
        $directOrdersQuery = Order::where('sku', $productSku);
        
        // Query for bundle orders that contain this product as a component
        $bundleOrdersQuery = Order::join('products as p', 'orders.sku', '=', 'p.sku')
            ->where('p.type', 'Bundle')
            ->where(function($query) use ($productSku) {
                $query->where('p.combination_sku_1', $productSku)
                    ->orWhere('p.combination_sku_2', $productSku);
            })
            ->select(
                'orders.*',
                DB::raw("'Bundle' as order_type"),
                'p.combination_sku_1',
                'p.combination_sku_2'
            );
            
        // Add direct orders with an order_type field
        $directOrdersQuery->select(
            'orders.*',
            DB::raw("'Direct' as order_type"),
            DB::raw("NULL as combination_sku_1"),
            DB::raw("NULL as combination_sku_2")
        );
        
        // Combine both queries
        $ordersQuery = $directOrdersQuery->union($bundleOrdersQuery);
        
        // Apply sales channel filter if provided
        if (request('sales_channel')) {
            $ordersQuery = DB::query()->fromSub($ordersQuery, 'combined_orders')
                ->where('sales_channel_id', request('sales_channel'));
        }
        
        // Apply month filter if provided
        if (request('month')) {
            $selectedMonth = $request->input('month', date('Y-m'));
            $ordersQuery = DB::query()->fromSub($ordersQuery, 'combined_orders')
                ->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
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
            ->addColumn('order_source', function ($order) use ($productSku) {
                if ($order->order_type == 'Direct') {
                    return '<span class="badge badge-primary">Direct Order</span>';
                } else {
                    // Highlight which combination position this product is in
                    if ($order->combination_sku_1 == $productSku) {
                        return '<span class="badge badge-info">Bundle (Component 1)</span>';
                    } else {
                        return '<span class="badge badge-success">Bundle (Component 2)</span>';
                    }
                }
            })
            ->rawColumns(['total_price', 'order_source'])
            ->make(true);
    }

    // Updated getSalesMetrics method to include bundle orders
    public function getSalesMetrics(Product $product, Request $request)
    {
        $salesChannelId = request('sales_channel');
        $month = $request->input('month');
        $productSku = $product->sku;

        // Base query conditions (date and sales channel filters)
        $dateCondition = [];
        if ($month) {
            $dateCondition = [
                date('Y', strtotime($month)), 
                date('m', strtotime($month))
            ];
        }

        // Query for direct orders
        $directOrdersQuery = Order::where('sku', $productSku);
        
        // Apply filters to direct orders
        if ($salesChannelId) {
            $directOrdersQuery->where('sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $directOrdersQuery->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
        }
        
        // Query for bundle orders containing this product
        $bundleOrdersQuery = Order::join('products as p', 'orders.sku', '=', 'p.sku')
            ->where('p.type', 'Bundle')
            ->where(function($query) use ($productSku) {
                $query->where('p.combination_sku_1', $productSku)
                    ->orWhere('p.combination_sku_2', $productSku);
            });
            
        // Apply filters to bundle orders
        if ($salesChannelId) {
            $bundleOrdersQuery->where('orders.sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $bundleOrdersQuery->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', $dateCondition);
        }
        
        // Get metrics for direct orders
        $directOrderCount = (clone $directOrdersQuery)->count();
        $directOrderAmount = (clone $directOrdersQuery)->sum('amount');
        $directUniqueCustomers = (clone $directOrdersQuery)
            ->where('customer_name', 'NOT LIKE', '%*%')
            ->where('customer_phone_number', 'NOT LIKE', '%*%')
            ->select('customer_name', 'customer_phone_number')
            ->distinct()
            ->count();
            
        // Get metrics for bundle orders
        $bundleOrderCount = (clone $bundleOrdersQuery)->count();
        $bundleOrderAmount = (clone $bundleOrdersQuery)->sum('orders.amount');
        $bundleUniqueCustomers = (clone $bundleOrdersQuery)
            ->where('orders.customer_name', 'NOT LIKE', '%*%')
            ->where('orders.customer_phone_number', 'NOT LIKE', '%*%')
            ->select('orders.customer_name', 'orders.customer_phone_number')
            ->distinct()
            ->count();
            
        // Combine metrics
        $totalOrdersCount = $directOrderCount + $bundleOrderCount;
        $totalAmountSum = $directOrderAmount + $bundleOrderAmount;
        
        // Unique customers might overlap between direct and bundle, so we need a combined query
        $uniqueCustomersQuery = DB::table(function($query) use ($productSku, $salesChannelId, $dateCondition) {
            // Direct orders customers
            $query->from('orders')
                ->where('sku', $productSku)
                ->select('customer_name', 'customer_phone_number');
                
            // Apply filters
            if ($salesChannelId) {
                $query->where('sales_channel_id', $salesChannelId);
            }
            if (!empty($dateCondition)) {
                $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
            }
            
            // Union with bundle orders customers
            $query->union(
                DB::table('orders')
                    ->join('products as p', 'orders.sku', '=', 'p.sku')
                    ->where('p.type', 'Bundle')
                    ->where(function($subquery) use ($productSku) {
                        $subquery->where('p.combination_sku_1', $productSku)
                                ->orWhere('p.combination_sku_2', $productSku);
                    })
                    ->when($salesChannelId, function($subquery) use ($salesChannelId) {
                        return $subquery->where('orders.sales_channel_id', $salesChannelId);
                    })
                    ->when(!empty($dateCondition), function($subquery) use ($dateCondition) {
                        return $subquery->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', $dateCondition);
                    })
                    ->select('orders.customer_name', 'orders.customer_phone_number')
            );
        })
        ->where('customer_name', 'NOT LIKE', '%*%')
        ->where('customer_phone_number', 'NOT LIKE', '%*%')
        ->distinct()
        ->count();
        
        // Calculate totals and averages
        $uniqueCustomerCount = $uniqueCustomersQuery;
        $ordersPerCustomerRatio = $uniqueCustomerCount > 0 ? $totalOrdersCount / $uniqueCustomerCount : 0;
        $averageOrderValue = $totalOrdersCount > 0 ? $totalAmountSum / $totalOrdersCount : 0;
        
        // Calculate daily average orders
        $avgDailyOrdersQuery = DB::table(function($query) use ($productSku, $salesChannelId, $dateCondition) {
            // Direct orders
            $directQuery = DB::table('orders')
                ->where('sku', $productSku)
                ->selectRaw('DATE(date) as order_date, COUNT(*) as order_count');
                
            // Apply filters
            if ($salesChannelId) {
                $directQuery->where('sales_channel_id', $salesChannelId);
            }
            if (!empty($dateCondition)) {
                $directQuery->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
            }
            
            $directQuery->groupBy('order_date');
            
            // Bundle orders
            $bundleQuery = DB::table('orders')
                ->join('products as p', 'orders.sku', '=', 'p.sku')
                ->where('p.type', 'Bundle')
                ->where(function($subquery) use ($productSku) {
                    $subquery->where('p.combination_sku_1', $productSku)
                            ->orWhere('p.combination_sku_2', $productSku);
                })
                ->selectRaw('DATE(orders.date) as order_date, COUNT(*) as order_count');
                
            // Apply filters
            if ($salesChannelId) {
                $bundleQuery->where('orders.sales_channel_id', $salesChannelId);
            }
            if (!empty($dateCondition)) {
                $bundleQuery->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', $dateCondition);
            }
            
            $bundleQuery->groupBy('order_date');
            
            // Combine both queries
            $query->from(DB::raw("({$directQuery->toSql()} UNION ALL {$bundleQuery->toSql()}) as combined_orders"))
                ->mergeBindings($directQuery)
                ->mergeBindings($bundleQuery)
                ->selectRaw('order_date, SUM(order_count) as total_count')
                ->groupBy('order_date');
        });
        
        $totalDaysWithOrders = $avgDailyOrdersQuery->count();
        $totalOrdersForAvg = $avgDailyOrdersQuery->sum('total_count');
        $avgDailyOrdersCount = $totalDaysWithOrders > 0 ? $totalOrdersForAvg / $totalDaysWithOrders : 0;
        
        // Calculate net profit for direct orders only (single product)
        $netProfit = (clone $directOrdersQuery)
            ->selectRaw('SUM(amount - ?) as net_profit', [$product->harga_cogs])
            ->value('net_profit') ?? 0;

        return response()->json([
            'uniqueCustomerCount' => $uniqueCustomerCount,
            'totalOrdersCount' => $totalOrdersCount,
            'directOrdersCount' => $directOrderCount,
            'bundleOrdersCount' => $bundleOrderCount,
            'totalAmountSum' => $totalAmountSum,
            'avgDailyOrdersCount' => round($avgDailyOrdersCount, 2),
            'ordersPerCustomerRatio' => round($ordersPerCustomerRatio, 2),
            'averageOrderValue' => round($averageOrderValue, 2),
            'netProfitSingleProduct' => $netProfit,
        ]);
    }

    // Updated getOrderCountBySku method to include bundle orders
    public function getOrderCountBySku(Product $product, Request $request)
    {
        $salesChannelId = request('sales_channel');
        $month = $request->input('month', date('Y-m'));
        $productSku = $product->sku;

        // Get direct orders for this product
        $directOrdersQuery = Order::where('sku', $productSku);
        
        // Apply filters to direct orders
        if ($salesChannelId) {
            $directOrdersQuery->where('sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $directOrdersQuery->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ]);
        }
        
        // Get bundle orders containing this product
        $bundleOrdersQuery = Order::join('products as p', 'orders.sku', '=', 'p.sku')
            ->where('p.type', 'Bundle')
            ->where(function($query) use ($productSku) {
                $query->where('p.combination_sku_1', $productSku)
                    ->orWhere('p.combination_sku_2', $productSku);
            });
            
        // Apply filters to bundle orders
        if ($salesChannelId) {
            $bundleOrdersQuery->where('orders.sales_channel_id', $salesChannelId);
        }
        if ($month) {
            $bundleOrdersQuery->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ]);
        }

        // Get direct order count
        $directOrderCount = $directOrdersQuery->count();
        
        // Get bundle orders grouped by SKU
        $bundleOrders = $bundleOrdersQuery
            ->select('orders.sku as bundle_sku', DB::raw('count(*) as count'))
            ->groupBy('orders.sku')
            ->get();
            
        // Prepare result with direct orders first
        $skuData = [
            [
                'sku' => $productSku . ' (Direct)',
                'count' => $directOrderCount
            ]
        ];
        
        // Add bundle orders
        foreach ($bundleOrders as $order) {
            $skuData[] = [
                'sku' => $order->bundle_sku . ' (Bundle)',
                'count' => $order->count
            ];
        }

        return response()->json($skuData);
    }

    // Updated getOrderCountPerDay method to include bundle orders
    /**
     * Get order count per day for a product, including both direct orders and bundle usage
     */
    public function getOrderCountPerDay(Product $product, Request $request)
    {
        $type = request('type', 'daily');
        $salesChannel = request('sales_channel');
        $month = $request->input('month', date('Y-m'));
        $productSku = $product->sku;

        // Date condition
        $dateCondition = [];
        if ($month) {
            $dateCondition = [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ];
        }

        // For Single products, include both direct and bundle orders
        if ($product->type == 'Single') {
            // Get direct orders count per day
            $directOrders = DB::table('orders')
                ->where('sku', $productSku)
                ->when($salesChannel, function($query) use ($salesChannel) {
                    return $query->where('sales_channel_id', $salesChannel);
                })
                ->when($month, function($query) use ($dateCondition) {
                    return $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
                })
                ->selectRaw('DATE(date) as period, COUNT(id_order) as order_count')
                ->groupBy('period');
                
            // Get bundle orders count per day
            $bundleOrders = DB::table('orders')
                ->join('products as p', 'orders.sku', '=', 'p.sku')
                ->where('p.type', 'Bundle')
                ->where(function($query) use ($productSku) {
                    $query->where('p.combination_sku_1', $productSku)
                        ->orWhere('p.combination_sku_2', $productSku);
                })
                ->when($salesChannel, function($query) use ($salesChannel) {
                    return $query->where('orders.sales_channel_id', $salesChannel);
                })
                ->when($month, function($query) use ($dateCondition) {
                    return $query->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', $dateCondition);
                })
                ->selectRaw('DATE(orders.date) as period, COUNT(orders.id_order) as order_count')
                ->groupBy('period');
                
            // Get SQL for both queries
            $directSql = $directOrders->toSql();
            $bundleSql = $bundleOrders->toSql();
            
            // Get bindings for both queries
            $directBindings = $directOrders->getBindings();
            $bundleBindings = $bundleOrders->getBindings();
            
            // Combine the results using a raw expression
            $combinedOrders = DB::table(DB::raw("($directSql UNION ALL $bundleSql) as combined_orders"))
                ->mergeBindings($directOrders)
                ->mergeBindings($bundleOrders)
                ->selectRaw('period, SUM(order_count) as total_count')
                ->groupBy('period')
                ->orderBy('period', 'asc')
                ->get();
                
            $labels = $combinedOrders->pluck('period');
            $data = $combinedOrders->pluck('total_count');
            
            return response()->json([
                'labels' => $labels,
                'data' => $data
            ]);
        } else {
            // For non-Single products, use the original logic
            $query = DB::table('orders')
                ->where('sku', $productSku);

            if (!is_null($salesChannel)) {
                $query->where('sales_channel_id', $salesChannel);
            }
            
            if ($month) {
                $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
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
    }

    /**
     * Get order count by sales channel, including both direct orders and bundle usage
     */
    public function getOrderCountBySalesChannel($productId, Request $request)
    {
        $product = Product::findOrFail($productId);
        $productSku = $product->sku;
        $salesChannelId = request('sales_channel');
        $month = $request->input('month', date('Y-m'));
        
        // Set date conditions if month provided
        $dateCondition = [];
        if ($month) {
            $dateCondition = [
                date('Y', strtotime($month)),
                date('m', strtotime($month))
            ];
        }

        // For single products, include both direct orders and bundle usage
        if ($product->type == 'Single') {
            // Query for direct orders by sales channel
            $directOrdersQuery = Order::where('sku', $productSku)
                ->when($salesChannelId, function($query) use ($salesChannelId) {
                    return $query->where('sales_channel_id', $salesChannelId);
                })
                ->when($month, function($query) use ($dateCondition) {
                    return $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
                })
                ->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
                ->select(
                    'sales_channels.name as sales_channel',
                    DB::raw('COUNT(orders.id) as order_count'),
                    DB::raw("'direct' as order_type")
                )
                ->groupBy('sales_channels.id', 'sales_channels.name');
            
            // Query for bundle orders by sales channel
            $bundleOrdersQuery = Order::join('products as p', 'orders.sku', '=', 'p.sku')
                ->where('p.type', 'Bundle')
                ->where(function($query) use ($productSku) {
                    $query->where('p.combination_sku_1', $productSku)
                        ->orWhere('p.combination_sku_2', $productSku);
                })
                ->when($salesChannelId, function($query) use ($salesChannelId) {
                    return $query->where('orders.sales_channel_id', $salesChannelId);
                })
                ->when($month, function($query) use ($dateCondition) {
                    return $query->whereRaw('YEAR(orders.date) = ? AND MONTH(orders.date) = ?', $dateCondition);
                })
                ->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
                ->select(
                    'sales_channels.name as sales_channel',
                    DB::raw('COUNT(orders.id) as order_count'),
                    DB::raw("'bundle' as order_type")
                )
                ->groupBy('sales_channels.id', 'sales_channels.name');
                
            // Combine both queries with union
            $combinedQuery = $directOrdersQuery->union($bundleOrdersQuery);
            
            // Get the combined counts grouped by sales channel
            $orderCounts = DB::query()
                ->fromSub($combinedQuery, 'combined_orders')
                ->select(
                    'sales_channel',
                    DB::raw('SUM(order_count) as total_count'),
                    'order_type'
                )
                ->groupBy('sales_channel', 'order_type')
                ->get();
                
            // Organize data for the stacked bar chart
            $channels = $orderCounts->pluck('sales_channel')->unique();
            
            $directData = [];
            $bundleData = [];
            
            foreach ($channels as $channel) {
                $directItem = $orderCounts->where('sales_channel', $channel)
                                        ->where('order_type', 'direct')
                                        ->first();
                
                $bundleItem = $orderCounts->where('sales_channel', $channel)
                                        ->where('order_type', 'bundle')
                                        ->first();
                                        
                $directData[] = $directItem ? $directItem->total_count : 0;
                $bundleData[] = $bundleItem ? $bundleItem->total_count : 0;
            }
            
            return response()->json([
                'labels' => $channels,
                'datasets' => [
                    [
                        'label' => 'Direct Orders',
                        'data' => $directData,
                        'backgroundColor' => 'rgba(54, 162, 235, 0.6)'
                    ],
                    [
                        'label' => 'Bundle Usage',
                        'data' => $bundleData,
                        'backgroundColor' => 'rgba(75, 192, 192, 0.6)'
                    ]
                ]
            ]);
        } else {
            // For non-single products, use the original logic
            $orderCounts = Order::where('sku', $product->sku)
                ->when($salesChannelId, function($query) use ($salesChannelId) {
                    return $query->where('sales_channel_id', $salesChannelId);
                })
                ->when($month, function($query) use ($dateCondition) {
                    return $query->whereRaw('YEAR(date) = ? AND MONTH(date) = ?', $dateCondition);
                })
                ->join('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
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

    public function getMarketingMetrics(Product $product)
    {
        $talentContentCount = TalentContent::where('sku', $product->sku)->count();
        
        $uniqueTalentIdCount = TalentContent::where('sku', $product->sku)
            ->distinct('talent_id')
            ->count('talent_id');

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
