<?php

namespace App\Domain\Order\Controllers;

use App\Domain\Order\BLL\Order\OrderBLLInterface;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\Exports\OrdersExport;
use App\Domain\Order\Exports\SkuQuantitiesExport;
use App\Domain\Order\Exports\UniqueSkuExport;
use App\Domain\Order\Exports\OrderTemplateExport;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Models\DailyHpp;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\SalesChannel;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\Domain\Customer\Models\CustomersAnalysis;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use GuzzleHttp\Client;
use Carbon\Carbon;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;
use Illuminate\Support\Facades\DB;
use App\Domain\Sales\Services\GoogleSheetService;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    protected $googleSheetService;

    public function __construct(
        protected OrderBLLInterface $orderBLL,
        protected OrderDALInterface $orderDAL,
        protected SalesChannelBLLInterface $salesChannelBLL,
        GoogleSheetService $googleSheetService
    ) { 
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAnyOrder', Order::class);

        $orderQuery = $this->orderBLL->getOrderDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($orderQuery)
            ->addColumn('salesChannel', function ($row) {
                return $row->salesChannel->name ?? '-';
            })
            ->addColumn('qtyFormatted', function ($row) {
                return number_format($row->qty, 0, ',', '.');
            })
            ->addColumn('priceFormatted', function ($row) {
                return number_format($row->amount, 0, ',', '.');
            })
            ->editColumn('process_at', function ($row) {
                return $row->process_at ? Carbon::parse($row->process_at)->isoFormat('DD MMM YYYY') : null;
            })
            ->addColumn(
                'actions',
                '<a href="{{ URL::route( \'order.show\', array( $id )) }}" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-success btn-sm updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>'
            )
            ->addColumn(
                'view_only',
                '<a href="{{ URL::route( \'order.show\', array( $id )) }}" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>'
            )
            ->rawColumns(['actions', 'view_only'])
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnyOrder', Order::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();
        $cities = Order::select('city')->distinct()->orderBy('city')->get();
        $status = Order::select('status')
            ->distinct()
            ->whereNotNull('status')
            ->orderBy('status')
            ->get();

        return view('admin.order.index', compact('salesChannels', 'cities', 'status'));
    }

    public function showDemography() {
        return view('admin.report.index');
    }

    /**
     * Create new order
     */
    public function store(OrderStoreRequest $request): JsonResponse
    {
        $this->authorize('createOrder', Order::class);

        return response()->json($this->orderBLL->createOrder($request, Auth::user()->current_tenant_id));
    }

    /**
     * Create new order
     */
    public function show(Order $order): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnyOrder', Order::class);

        $order = $order->load('salesChannel');

        return view('admin.order.show', compact('order'));
    }

    /**
     * Update an order
     */
    public function update(Order $order, OrderStoreRequest $request): JsonResponse
    {
        $this->authorize('updateOrder', Order::class);

        $this->orderBLL->updateOrder($order, $request);

        return response()->json($request->all());
    }

    /**
     * Delete order
     */
    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('deleteOrder', Order::class);
        $this->orderBLL->deleteOrder($order);
        return response()->json(['message' => trans('messages.success_delete')]);
    }

    /**
     * Export order
     */
    public function export(Request $request): Response|BinaryFileResponse
    {
        $this->authorize('viewAnyOrder', Order::class);

        return (new OrdersExport(Auth::user()->current_tenant_id))->forPeriod($request->date)->download('orders.xlsx');
    }

    /**
     * Template import order
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('createOrder', Order::class);

        return Excel::download(new OrderTemplateExport(), 'Order Template.xlsx');
    }

    /**
     * Import order
     */
    public function import(Request $request): void
    {
        $this->authorize('createOrder', Order::class);
        $this->orderBLL->importOrder($request, Auth::user()->current_tenant_id);
    }
    public function product() {
        return view('admin.order.product.index');
    }
    public function getPerformanceData(): JsonResponse
    {
        // Retrieve the data from the database using the Order model
        $orders = Order::select('sku')->get();

        // Initialize counts
        $counts = [
            'XFO' => 0,
            'RS' => 0,
            'CLNDLA' => 0,
            'HYLU' => 0,
        ];

        // Initialize last three numbers count
        $lastThreeNumbersCounts = [];

        // Calculate counts
        foreach ($orders as $order) {
            foreach ($counts as $key => $value) {
                if (strpos($order->sku, $key) !== false) {
                    $counts[$key]++;
                }
            }
            $lastThreeNumbers = substr($order->sku, -3);
            if (!isset($lastThreeNumbersCounts[$lastThreeNumbers])) {
                $lastThreeNumbersCounts[$lastThreeNumbers] = 0;
            }
            $lastThreeNumbersCounts[$lastThreeNumbers]++;
        }

        return new JsonResponse([
            'counts' => $counts,
            'lastThreeNumbersCounts' => $lastThreeNumbersCounts,
        ]);
    }
    public function fetchExternalOrders(): JsonResponse
    {
        $client = new Client();
        $baseUrl = 'https://wms-api.clerinagroup.com/v1/open/orders/page';
        $headers = [
            'x-api-key' => 'f5c80067e1da48e0b2b124558f5c533f1fda9fea72aa4a2a866c6a15a1a31ca8'
        ];

        try {
            $page = 1;
            $totalPages = 1;

            do {
                $response = $client->get($baseUrl, [
                    'headers' => $headers,
                    'query' => [
                        'status' => 'paid',
                        'page' => $page
                    ]
                ]);

                $data = json_decode($response->getBody()->getContents(), true);

                if ($page === 1) {
                    $totalPages = $data['metadata']['total_page'] ?? 1;
                }

                if (!isset($data['data'])) {
                    return response()->json(['error' => 'Unexpected response format', 'response' => $data], 500);
                }

                foreach ($data['data'] as $orderData) {
                    // Convert datetime strings to MySQL-compatible format
                    $date = $this->convertToMySQLDateTime($orderData['created_at']);
                    $createdAt = $this->convertToMySQLDateTime($orderData['created_at']);

                    // Transform and save data to the orders table using updateOrCreate
                    Order::updateOrCreate(
                        ['id_order' => $orderData['reference_no']],
                        [
                            'date' => $date,
                            'sales_channel_id' => $this->getSalesChannelId($orderData['channel_name']),
                            'customer_name' => $orderData['customer_name'],
                            'customer_phone_number' => $orderData['customer_phone'],
                            'product' => $orderData['product_summary'],
                            'qty' => $orderData['qty'],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                            'receipt_number' => $orderData['reference_no'],
                            'shipment' => $orderData['courier'],
                            'payment_method' => $orderData['courier_label'],
                            'sku' => $orderData['product_summary'],
                            'price' => $orderData['amount'],
                            'shipping_address' => $orderData['integration_store'],
                            'amount' => $orderData['amount'] - $orderData['shipping_fee'],
                            'username' => $orderData['channel_name'],
                            'tenant_id' => $this->determineTenantId($orderData['channel_name'], $orderData['product_summary'], $orderData['integration_store']),
                        ]
                    );
                }

                $page++;
            } while ($page <= $totalPages);

            return response()->json(['message' => 'Orders fetched and saved successfully']);
        } catch (Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function updateSalesTurnover()
    {
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        
        $totals = Order::select(DB::raw('date, SUM(amount) AS total_amount'))
                        ->where('tenant_id', 1)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereNotIn('status', [
                            'Batal', 
                            'cancelled', 
                            'Canceled', 
                            'Pembatalan diajukan', 
                            'Dibatalkan Sistem'
                        ])
                        ->groupBy('date')
                        ->get();
        
        foreach ($totals as $total) {
            $formattedDate = Carbon::parse($total->date)->format('Y-m-d');
                
                // Get the current sales record
                $salesRecord = DB::table('sales')
                    ->where('date', $formattedDate)
                    ->where('tenant_id', 1)
                    ->first();
                
                if ($salesRecord) {
                    // Calculate new ROAS if ad_spent_total exists and is not zero
                    $newRoas = 0;
                    if ($salesRecord->ad_spent_total > 0) {
                        $newRoas = $total->total_amount / $salesRecord->ad_spent_total;
                    }
                    
                    // Update sales record with new turnover and ROAS
                    DB::table('sales')
                        ->where('date', $formattedDate)
                        ->where('tenant_id', 1)
                        ->update([
                            'turnover' => $total->total_amount,
                            'roas' => $newRoas
                        ]);
                } else {
                    // Create new sales record if it doesn't exist
                    DB::table('sales')->insert([
                        'date' => $formattedDate,
                        'tenant_id' => 1,
                        'turnover' => $total->total_amount,
                        'roas' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            
            DB::table('net_profits')
                ->where('date', $formattedDate)
                ->where('tenant_id', 1)
                ->update(['sales' => $total->total_amount]);
        }
        
        return response()->json(['message' => 'Net profits sales updated successfully']);
    }
    public function updateSalesTurnover2()
    {
        $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        
        $totals = Order::select(DB::raw('date, SUM(amount) AS total_amount'))
                        ->where('tenant_id', 2)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->whereNotIn('status', [
                            'Batal', 
                            'cancelled', 
                            'Canceled', 
                            'Pembatalan diajukan', 
                            'Dibatalkan Sistem'
                        ])
                        ->groupBy('date')
                        ->get();
        
        foreach ($totals as $total) {
            $formattedDate = Carbon::parse($total->date)->format('Y-m-d');
                
                // Get the current sales record
                $salesRecord = DB::table('sales')
                    ->where('date', $formattedDate)
                    ->where('tenant_id', 2)
                    ->first();
                
                if ($salesRecord) {
                    // Calculate new ROAS if ad_spent_total exists and is not zero
                    $newRoas = 0;
                    if ($salesRecord->ad_spent_total > 0) {
                        $newRoas = $total->total_amount / $salesRecord->ad_spent_total;
                    }
                    
                    // Update sales record with new turnover and ROAS
                    DB::table('sales')
                        ->where('date', $formattedDate)
                        ->where('tenant_id', 2)
                        ->update([
                            'turnover' => $total->total_amount,
                            'roas' => $newRoas
                        ]);
                } else {
                    // Create new sales record if it doesn't exist
                    DB::table('sales')->insert([
                        'date' => $formattedDate,
                        'tenant_id' => 2,
                        'turnover' => $total->total_amount,
                        'roas' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            
            DB::table('net_profits')
                ->where('date', $formattedDate)
                ->where('tenant_id', 2)
                ->update(['sales' => $total->total_amount]);
        }
        
        return response()->json(['message' => 'Net profits sales updated successfully']);
    }
    public function updateSalesTurnoverAzrina()
    {
        try {
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            $tenant_id = 2; // Specifically for tenant_id 2
            
            // Get daily sales totals from orders
            $totals = Order::select(DB::raw('DATE(date) as date, SUM(amount) AS total_amount'))
                            ->where('tenant_id', $tenant_id)
                            ->whereBetween('date', [$startDate, $endDate])
                            ->whereNotIn('status', [
                                'Batal', 
                                'cancelled', 
                                'Canceled', 
                                'Pembatalan diajukan', 
                                'Dibatalkan Sistem'
                            ])
                            ->groupBy('date')
                            ->get();
            
            // Create a collection of formatted dates for the whereNotIn clause
            $formattedDates = collect();
            
            // Loop through results and update sales table
            foreach ($totals as $total) {
                $formattedDate = Carbon::parse($total->date)->format('Y-m-d');
                
                // Get the current sales record
                $salesRecord = DB::table('sales')
                    ->where('date', $formattedDate)
                    ->where('tenant_id', $tenant_id)
                    ->first();
                
                if ($salesRecord) {
                    // Calculate new ROAS if ad_spent_total exists and is not zero
                    $newRoas = 0;
                    if ($salesRecord->ad_spent_total > 0) {
                        $newRoas = $total->total_amount / $salesRecord->ad_spent_total;
                    }
                    
                    // Update sales record with new turnover and ROAS
                    DB::table('sales')
                        ->where('date', $formattedDate)
                        ->where('tenant_id', $tenant_id)
                        ->update([
                            'turnover' => $total->total_amount,
                            'roas' => $newRoas
                        ]);
                } else {
                    // Create new sales record if it doesn't exist
                    DB::table('sales')->insert([
                        'date' => $formattedDate,
                        'tenant_id' => $tenant_id,
                        'turnover' => $total->total_amount,
                        'roas' => 0,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }
            
            // Handle dates where there are no orders but sales records exist
            if ($formattedDates->isNotEmpty()) {
                DB::table('sales')
                    ->where('tenant_id', $tenant_id)
                    ->whereBetween('date', [$startDate, $endDate])
                    ->whereNotIn('date', $formattedDates->toArray())
                    ->update([
                        'turnover' => 0,
                        'roas' => 0
                    ]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Sales turnover and ROAS updated successfully for tenant ID 2'
            ]);
        } catch (\Exception $e) {
            \Log::error('Update Sales Turnover Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    private function processSku($sku)
    {
        if (strpos($sku, '1 ') === 0) {
            return substr($sku, 2); 
        }
        return $sku;
    }

    public function webhook(Request $request) 
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true) ?: [];
        $signature = $request->header('X-Webhook-Signature');
        
        if ($signature !== config('services.webhook.secret')) {
            Log::warning('Invalid webhook key');
            return response()->json(['message' => 'Invalid key'], 401);
        }
        
        Log::info('Webhook received', [
            'payload' => $data,
            'headers' => $request->header()
        ]);
        
        try {
            $this->processWebhook($data);
            return response()->json(['message' => 'Webhook processed successfully'], 200);
        } catch (\Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    private function processWebhook(array $data)
    {
        $eventType = $data['event_type'] ?? null;
        
        switch ($eventType) {
            case 'payment.successful':
                Log::info('Payment successful event received', $data);
                break;
            case 'subscription.updated':
                Log::info('Subscription updated event received', $data);
                break;
            default:
                Log::warning('Unknown webhook event type', ['event_type' => $eventType]);
        }
    }
    public function updateStatus(Request $request) 
    {
        $payload = $request->getContent();
        $data = json_decode($payload, true) ?: [];
        
        $signature = $request->header('X-Webhook-Signature');
        
        if ($signature !== config('services.webhook.secret')) {
            Log::warning('Invalid webhook key');
            return response()->json(['message' => 'Invalid key'], 401);
        }
        
        Log::info('Status update webhook received', [
            'payload' => $data
        ]);
        
        try {
            $order = Order::where('id_order', $data['id_order'])->first();
            
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->status = $data['data']['status'];
            $order->save();

            return response()->json([
                'message' => 'Order status updated successfully',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }

    public function updateDateAmount(Request $request) 
    {
        // Get raw payload
        $payload = $request->getContent();
        $data = json_decode($payload, true) ?: [];
        
        $signature = $request->header('X-Webhook-Signature');
        
        if ($signature !== config('services.webhook.secret')) {
            Log::warning('Invalid webhook key');
            return response()->json(['message' => 'Invalid key'], 401);
        }
        
        Log::info('Date and amount update webhook received', [
            'payload' => $data
        ]);
        
        try {
            $order = Order::where('id_order', $data['id_order'])->first();
            
            if (!$order) {
                return response()->json(['message' => 'Order not found'], 404);
            }

            $order->update([
                'date' => $data['data']['date'],
                'amount' => $data['data']['amount']
            ]);

            return response()->json([
                'message' => 'Order date and amount updated successfully',
                'data' => $order
            ], 200);

        } catch (\Exception $e) {
            Log::error('Webhook processing failed', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Webhook processing failed'], 500);
        }
    }
    public function fetchAllOrders(): JsonResponse
    {
        set_time_limit(0);
    
        $client = new Client();
        $baseUrl = 'https://wms-api.clerinagroup.com/v1/open/orders/page';
        $headers = [
            'x-api-key' => '29baec8f417f44c7ac981680fcaee5a070c7ad7320ea439fb5bf28150a1310ad'
        ];
        
        $startDate = Carbon::now()->subDays(2)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        // $startDate = '2025-03-07';
        // $endDate = '2025-03-08';
    
        try {
            $page = 1;
            $totalPages = 1;
    
            do {
                $response = $client->get($baseUrl, [
                    'headers' => $headers,
                    'query' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'page' => $page
                    ]
                ]);
    
                if ($response->getStatusCode() !== 200) {
                    return response()->json([
                        'error' => 'Failed to fetch data from API', 
                        'status_code' => $response->getStatusCode()
                    ], 500);
                }
    
                $data = json_decode($response->getBody()->getContents(), true);
    
                if ($page === 1) {
                    $totalPages = $data['metadata']['total_page'] ?? 1;
                }
    
                if (!isset($data['data'])) {
                    return response()->json([
                        'error' => 'Unexpected response format', 
                        'response' => $data
                    ], 500);
                }
                $filteredOrders = array_filter($data['data'], function($orderData) {
                    return $orderData['status'] !== 'cancelled';
                });
    
                foreach ($filteredOrders as $orderData) {
                    $orderData['product_summary'] = $this->processSku($orderData['product_summary']);
    
                    $date = $this->convertToMySQLDateTime($orderData['order_at']);
                    $createdAt = $this->convertToMySQLDateTime($orderData['created_at']);
                    $processAt = $this->convertToMySQLDateTime($orderData['process_at']);
    
                    $existingOrder = Order::where('id_order', $orderData['reference_no'])->first();
    
                    if ($existingOrder) {
                        $amount = $orderData['cogs'];
                        $amount = $amount < 0 ? 0 : $amount;
    
                        $existingOrder->update([
                            'amount' => $amount,
                            'sku' => $orderData['product_summary'],
                            'sales_channel_id' => $this->getSalesChannelId($orderData['channel_name']),
                            'tenant_id' => $this->determineTenantId($orderData['channel_name'], $orderData['product_summary'], $orderData['integration_store']),
                            'process_at' => $processAt,
                        ]);
                    } else {
                        Order::create([
                            'id_order' => $orderData['reference_no'],
                            'date' => $date,
                            'process_at' => $processAt,
                            'sales_channel_id' => $this->getSalesChannelId($orderData['channel_name']),
                            'customer_name' => $orderData['customer_name'],
                            'customer_phone_number' => $orderData['customer_phone'],
                            'product' => $orderData['product_summary'],
                            'qty' => $orderData['qty'],
                            'created_at' => $createdAt,
                            'updated_at' => $createdAt,
                            'receipt_number' => $orderData['reference_no'],
                            'shipment' => $orderData['courier'],
                            'payment_method' => $orderData['courier_label'],
                            'sku' => $orderData['product_summary'],
                            'cogs' => $orderData['cogs'],
                            'price' => $orderData['amount'],
                            'is_booking' => $orderData['is_booking'],
                            'status' => $orderData['status'],
                            'shipping_address' => $orderData['integration_store'],
                            'amount' => $orderData['cogs'],
                            'username' => $orderData['customer_username'],
                            'tenant_id' => $this->determineTenantId($orderData['channel_name'], $orderData['product_summary'], $orderData['integration_store']),
                        ]);
                    }
                }
    
                $page++;
            } while ($page <= $totalPages);
    
            return response()->json(['message' => 'Orders fetched and saved successfully']);
    
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error processing orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function updateProcessAtThisMonth(): JsonResponse
    {
        set_time_limit(0);

        $client = new Client();
        $baseUrl = 'https://wms-api.clerinagroup.com/v1/open/orders/page';
        $headers = [
            'x-api-key' => '29baec8f417f44c7ac981680fcaee5a070c7ad7320ea439fb5bf28150a1310ad'
        ];
        
        $startDate = '2025-01-12';
        $endDate = '2025-01-15';

        try {
            $page = 1;
            $totalPages = 1;
            $updatedCount = 0;

            do {
                $response = $client->get($baseUrl, [
                    'headers' => $headers,
                    'query' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'page' => $page
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return response()->json([
                        'error' => 'Failed to fetch data from API', 
                        'status_code' => $response->getStatusCode()
                    ], 500);
                }

                $data = json_decode($response->getBody()->getContents(), true);

                if ($page === 1) {
                    $totalPages = $data['metadata']['total_page'] ?? 1;
                }

                if (!isset($data['data'])) {
                    return response()->json([
                        'error' => 'Unexpected response format', 
                        'response' => $data
                    ], 500);
                }

                foreach ($data['data'] as $orderData) {
                    $processAt = $this->convertToMySQLDateTime($orderData['process_at']);
                    
                    // Only update if process_at is not the default value
                    if ($orderData['process_at'] !== '0001-01-01T00:00:00Z') {
                        $updated = Order::where('id_order', $orderData['reference_no'])
                            ->update([
                                'process_at' => $processAt
                            ]);
                        
                        if ($updated) {
                            $updatedCount++;
                        }
                    }
                }

                $page++;
            } while ($page <= $totalPages);

            return response()->json([
                'message' => 'Process dates updated successfully',
                'updated_count' => $updatedCount,
                'date_range' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error updating process dates',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function fetchUpdateStatus(): JsonResponse
    {
        set_time_limit(0);

        $client = new Client();
        $baseUrl = 'https://wms-api.clerinagroup.com/v1/open/orders/page';
        $headers = [
            'x-api-key' => 'f5c80067e1da48e0b2b124558f5c533f1fda9fea72aa4a2a866c6a15a1a31ca8'
        ];
        
        // $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
        // $endDate = Carbon::now()->format('Y-m-d');

        $startDate = '2024-12-28';
        $endDate = '2024-12-30';

        try {
            $page = 1;
            $totalPages = 1;

            do {
                $response = $client->get($baseUrl, [
                    'headers' => $headers,
                    'query' => [
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'page' => $page
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return response()->json([
                        'error' => 'Failed to fetch data from API', 
                        'status_code' => $response->getStatusCode()
                    ], 500);
                }

                $data = json_decode($response->getBody()->getContents(), true);

                if ($page === 1) {
                    $totalPages = $data['metadata']['total_page'] ?? 1;
                }

                if (!isset($data['data'])) {
                    return response()->json([
                        'error' => 'Unexpected response format', 
                        'response' => $data
                    ], 500);
                }

                foreach ($data['data'] as $orderData) {
                    Order::where('id_order', $orderData['reference_no'])
                        ->update(['status' => $orderData['status']]);
                }

                $page++;
            } while ($page <= $totalPages);

            return response()->json([
                'message' => 'Orders status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Error processing orders',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    private function getSalesChannelId($channelName)
    {
        return match ($channelName) {
            'Tiktok' => 4,
            'Shopee' => 1,
            'Lazada' => 2,
            'Manual'=> 5,
            'Tokopedia' => 3,
            null => 6,
            default => null,
        };
    }

    private function getTenantId($integrationStore)
    {
        if (strpos($integrationStore, 'Cleora') !== false) {
            return 1;
        } elseif (strpos($integrationStore, 'Azrina') !== false) {
            return 2;
        } else {
            return null;
        }
    }

    private function determineTenantId($channelName, $sku, $integrationStore)
    {
        if ($channelName === 'Manual') {
            return $this->getTenantIdBySku($sku);
        } else {
            return $this->getTenantId($integrationStore);
        }
    }


    private function getTenantIdBySku($sku)
{
    if (strpos($sku, 'AZ') !== false) {
        return 2;
    } elseif (strpos($sku, 'CL') !== false) {
        return 1;
    } else {
        return null;
    }
}

    private function convertToMySQLDateTime($dateTime)
    {
        $date = new \DateTime($dateTime);
        return $date->format('Y-m-d H:i:s');
    }

    public function getOrdersByDate(Request $request): JsonResponse
    {
        $orders = Order::with('salesChannel')
            ->where('tenant_id', Auth::user()->current_tenant_id)
            ->where('date', Carbon::parse($request->input('date')))
            ->orderBy('date', 'asc')
            ->get();

        $groupedOrders = $orders->groupBy(function($order) {
            return $order->salesChannel->name;
        });

        // Format the grouped data into an array with the sum of the amount
        $result = $groupedOrders->map(function ($orders, $salesChannelName) {
            return [
                'sales_channel' => $salesChannelName,
                'total_amount' => $orders->sum('amount'),
                'orders' => $orders
            ];
        })->values();
        return response()->json($result);
    }

    public function exportUniqueSku()
    {
        return Excel::download(new UniqueSkuExport, 'unique_skus.xlsx');
    }
    public function importOrdersCleora()
    {
        set_time_limit(0);
        $range = 'Orders Shopee Data!A2:I'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $orderData = [
                    'id_order'            => $row[0] ?? null,
                    'product'            => $row[3] ?? null,
                    'sku'            => $row[2] ?? null,
                    'username'            => $row[4] ?? null,
                    'customer_name'       => $row[5] ?? null, 
                    'customer_phone_number' => $row[6] ?? null,
                    'shipping_address'    => $row[7] ?? null,
                    'city'                => $row[8] ?? null, 
                    'province'            => $row[9] ?? null,
                    'tenant_id'           => $tenant_id,
                ];

                $order = Order::where('id_order', $orderData['id_order'])->first();

                if ($order) {
                    if (!empty($row[1])) {
                        $tanggal_pesanan_dibuat = Carbon::createFromFormat('Y-m-d H:i', $row[1])->format('Y-m-d H:i:s');
                    } else {
                        $tanggal_pesanan_dibuat = $order->date->format('Y-m-d H:i:s');
                    }
                    $existingRecord = CustomersAnalysis::where('tanggal_pesanan_dibuat', $tanggal_pesanan_dibuat)
                                                        ->where('nama_penerima', $orderData['customer_name'])
                                                        ->where('nomor_telepon', $orderData['customer_phone_number'])
                                                        ->first();
                    if (!$existingRecord) {
                        $qty = $order->qty ?? null;
                        $customersAnalysisData = [
                            'tanggal_pesanan_dibuat' => $tanggal_pesanan_dibuat,
                            'nama_penerima'          => $orderData['customer_name'],
                            'produk'                 => $orderData['product'],
                            'qty'                    => $qty,
                            'alamat'                 => $orderData['shipping_address'],
                            'kota_kabupaten'         => $orderData['city'],
                            'provinsi'               => $orderData['province'],
                            'nomor_telepon'          => $orderData['customer_phone_number'],
                            'tenant_id'              => $orderData['tenant_id'],
                            'sku'              => $orderData['sku'],
                            'sales_channel_id'       => 1,
                            'social_media_id'        => null,
                            'is_joined'              => 0,
                            'channel'              => "Shopee",
                            'created_at'             => now(),
                            'updated_at'             => now(), 
                        ];
                        CustomersAnalysis::create($customersAnalysisData);
                    }
                    $order->update($orderData);
                }

                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Data imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
        ]);
    }
    public function importCRMCustomer()
    {
        set_time_limit(0);
        $range = 'Closing Full!A2:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $duplicateRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Clean and validate phone number
                $phoneNumber = '';
                if (!empty($row[3])) {
                    // Remove any non-numeric characters from the phone number
                    $phoneNumber = preg_replace('/[^0-9]/', '', $row[3]);
                    
                    // Skip if phone number is empty after cleaning
                    if (empty($phoneNumber)) {
                        $skippedRows++;
                        continue;
                    }
                    
                    // Add a leading zero if it doesn't already have one
                    if (substr($phoneNumber, 0, 1) !== '0') {
                        $phoneNumber = '0' . $phoneNumber;
                    }
                } else {
                    $skippedRows++;
                    continue;
                }

                // Parse date from "dd/mm/yyyy" format to Y-m-d H:i:s
                if (!empty($row[0])) {
                    try {
                        $tanggal_pesanan_dibuat = Carbon::createFromFormat('d/m/Y', $row[0])->format('Y-m-d H:i:s');
                    } catch (\Exception $e) {
                        $tanggal_pesanan_dibuat = now()->format('Y-m-d H:i:s');
                    }
                } else {
                    $tanggal_pesanan_dibuat = now()->format('Y-m-d H:i:s');
                }

                // Check for duplicate before creating
                $existingRecord = CustomersAnalysis::where('tanggal_pesanan_dibuat', $tanggal_pesanan_dibuat)
                                                ->where('nama_penerima', $row[1] ?? null)
                                                ->where('nomor_telepon', $phoneNumber)
                                                ->where('produk', $row[4] ?? null)
                                                ->where('qty', $row[5] ?? null)
                                                ->where('admin_crm', $row[8] ?? null)
                                                ->first();
                
                if (!$existingRecord) {
                    // Extract city and province from address if possible
                    $alamat = $row[2] ?? '';
                    $kota = null;
                    $provinsi = null;
                    
                    // Try to extract province and city from address
                    if (preg_match('/KAB\.\s*([^,]+)|KOTA\s*([^,]+)/i', $alamat, $matches)) {
                        $kota = trim(isset($matches[1]) ? $matches[1] : $matches[2]);
                    }
                    
                    if (preg_match('/([A-Z\s]+),\s*ID/i', $alamat, $matches)) {
                        $provinsi = trim($matches[1]);
                    }

                    $customersAnalysisData = [
                        'tanggal_pesanan_dibuat' => $tanggal_pesanan_dibuat,
                        'nama_penerima'          => $row[1] ?? null, // Nama
                        'produk'                 => $row[4] ?? null,
                        'sku'                    => $row[4] ?? null,
                        'qty'                    => $row[5] ?? null, // Quantity
                        'alamat'                 => $alamat,         // Alamat
                        'kota_kabupaten'         => $kota,           // Extracted from address
                        'provinsi'               => $provinsi,       // Extracted from address
                        'nomor_telepon'          => $phoneNumber,    // Cleaned and formatted phone number
                        'tenant_id'              => $tenant_id,
                        'sales_channel_id'       => null,
                        'social_media_id'        => null,
                        'is_joined'              => 0,
                        'is_dormant'             => 0,
                        'status_customer'        => null,
                        'which_hp'               => null,
                        'channel'                => 'CRM Sales',
                        'admin_crm'              => $row[8] ?? null, 
                        'created_at'             => now(),
                        'updated_at'             => now(),
                    ];

                    CustomersAnalysis::create($customersAnalysisData);
                    $processedRows++;
                } else {
                    $duplicateRows++;
                }
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Customers analysis data created successfully',
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'duplicate_rows' => $duplicateRows,
            'skipped_rows' => $skippedRows
        ]);
    }
    // public function importOrdersTokopedia()
    // {
    //     set_time_limit(0);
    //     $range = 'Tokopedia Processed!A2:P'; 
    //     $sheetData = $this->googleSheetService->getSheetData($range);

    //     $tenant_id = 1;
    //     $chunkSize = 50;
    //     $totalRows = count($sheetData);
    //     $processedRows = 0;

    //     foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
    //         foreach ($chunk as $row) {
    //             $orderData = [
    //                 'date' => !empty($row[1]) ? Carbon::createFromFormat('d-m-Y H:i:s', $row[1])->format('Y-m-d') : null,
    //                 'process_at'           => null,
    //                 'id_order'             => $row[0] ?? null,
    //                 'sales_channel_id'     => 3, // Tokopedia
    //                 'customer_name'        => $row[7] ?? null,
    //                 'customer_phone_number' => $row[8] ?? null,
    //                 'product'              => $row[2] ?? null,
    //                 'qty'                  => $row[5] ?? null,
    //                 'receipt_number'       => $row[14] ?? null,
    //                 'shipment'             => $row[15] ?? null,
    //                 'payment_method'       => null,
    //                 'sku'                  => $row[4] ?? null,
    //                 'variant'              => $row[3] ?? null,
    //                 'price'                => $row[6] ?? null,
    //                 'username'             => $row[7] ?? null,
    //                 'shipping_address'     => $row[9] ?? null,
    //                 'city'                 => $row[10] ?? null,
    //                 'province'             => $row[11] ?? null,
    //                 'amount'               => $row[6] ?? null,
    //                 'tenant_id'            => $tenant_id,
    //                 'is_booking'           => 0,
    //                 'status'               => $row[13] ?? null,
    //                 'created_at'           => now(),
    //                 'updated_at'           => now(),
    //             ];

    //             // Check if order with the same id_order, product, sku exists
    //             $order = Order::where('id_order', $orderData['id_order'])
    //                         ->where('product', $orderData['product'])
    //                         ->where('sku', $orderData['sku'])
    //                         ->first();

    //             // If order doesn't exist, create it
    //             if (!$order) {
    //                 Order::create($orderData);
    //                 $processedRows++;
    //             }
    //         }
    //         usleep(100000); // Small delay to prevent overwhelming the server
    //     }

    //     return response()->json([
    //         'message' => 'Tokopedia orders imported successfully', 
    //         'total_rows' => $totalRows,
    //         'processed_rows' => $processedRows
    //     ]);
    // }
    public function importOrdersShopee()
    {
        set_time_limit(0);
        $range = 'Shopee Processed!A2:R'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[3])) {
                    $skippedRows++;
                    continue;
                }
                
                // Parse price by completely removing all dots and commas to store as integer
                $price = null;
                if (isset($row[14])) {
                    // Remove all dots (thousand separators) and any commas
                    $price = preg_replace('/[.,]/', '', $row[14]);
                    // Make sure it's a valid integer
                    $price = is_numeric($price) ? (int)$price : null;
                }
                
                // Parse amount similarly
                $amount = null;
                if (isset($row[14]) && isset($row[15])) {
                    $price_val = preg_replace('/[.,]/', '', $row[14]);
                    $shipping_val = preg_replace('/[.,]/', '', $row[15]);
                    
                    // Only calculate if both are numeric
                    if (is_numeric($price_val) && is_numeric($shipping_val)) {
                        $amount = (int)$price_val + (int)$shipping_val;
                    }
                }
                
                $orderData = [
                    'date'                 => Carbon::parse($row[3])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 1, // Shopee
                    'customer_name'        => $row[7] ?? null,
                    'customer_phone_number' => $row[8] ?? null,
                    'product'              => $row[5] ?? null,
                    'qty'                  => $row[12] ?? null,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[2] ?? null,
                    'payment_method'       => $row[13] ?? null,
                    'sku'                  => $row[4] ?? null,
                    'variant'              => null,
                    'price'                => $price,
                    'username'             => $row[6] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount'               => $amount,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[17] ?? null, // Column R
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->where('amount', $orderData['amount'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->sku = $orderData['sku'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Shopee orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function importOrdersShopee2()
    {
        set_time_limit(0);
        $range = 'Shopee Processed 2!A2:R'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[3])) {
                    $skippedRows++;
                    continue;
                }

                // Parse price by completely removing all dots and commas to store as integer
                $price = null;
                if (isset($row[14])) {
                    // Remove all dots (thousand separators) and any commas
                    $price = preg_replace('/[.,]/', '', $row[14]);
                    // Make sure it's a valid integer
                    $price = is_numeric($price) ? (int)$price : null;
                }
                
                // Parse amount similarly
                $amount = null;
                if (isset($row[14]) && isset($row[15])) {
                    $price_val = preg_replace('/[.,]/', '', $row[14]);
                    $shipping_val = preg_replace('/[.,]/', '', $row[15]);
                    
                    // Only calculate if both are numeric
                    if (is_numeric($price_val) && is_numeric($shipping_val)) {
                        $amount = (int)$price_val + (int)$shipping_val;
                    }
                }
                
                $orderData = [
                    'date'                 => Carbon::parse($row[3])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 8, // Shopee
                    'customer_name'        => $row[7] ?? null,
                    'customer_phone_number' => $row[8] ?? null,
                    'product'              => $row[5] ?? null,
                    'qty'                  => $row[12] ?? null,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[2] ?? null,
                    'payment_method'       => $row[13] ?? null,
                    'sku'                  => $row[4] ?? null,
                    'variant'              => null,
                    'price'                => $row[14] ?? null,
                    'username'             => $row[6] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount'               => $amount,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[17] ?? null, // Column R
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->where('amount', $orderData['amount'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->sku = $orderData['sku'];
                        $order->amount = $orderData['amount'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Shopee orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function importOrdersShopee3()
    {
        set_time_limit(0);
        $range = 'Shopee Processed 3!A2:R'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[3])) {
                    $skippedRows++;
                    continue;
                }

                // Parse price by completely removing all dots and commas to store as integer
                $price = null;
                if (isset($row[14])) {
                    // Remove all dots (thousand separators) and any commas
                    $price = preg_replace('/[.,]/', '', $row[14]);
                    // Make sure it's a valid integer
                    $price = is_numeric($price) ? (int)$price : null;
                }
                
                // Parse amount similarly
                $amount = null;
                if (isset($row[14]) && isset($row[15])) {
                    $price_val = preg_replace('/[.,]/', '', $row[14]);
                    $shipping_val = preg_replace('/[.,]/', '', $row[15]);
                    
                    // Only calculate if both are numeric
                    if (is_numeric($price_val) && is_numeric($shipping_val)) {
                        $amount = (int)$price_val + (int)$shipping_val;
                    }
                }
                
                $orderData = [
                    'date'                 => Carbon::parse($row[3])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 9,
                    'customer_name'        => $row[7] ?? null,
                    'customer_phone_number' => $row[8] ?? null,
                    'product'              => $row[5] ?? null,
                    'qty'                  => $row[12] ?? null,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[2] ?? null,
                    'payment_method'       => $row[13] ?? null,
                    'sku'                  => $row[4] ?? null,
                    'variant'              => null,
                    'price'                => $row[14] ?? null,
                    'username'             => $row[6] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount' => isset($row[16]) ? ($row[16]) : null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[17] ?? null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->where('amount', $orderData['amount'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->sku = $orderData['sku'];
                        $order->amount = $orderData['amount'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Shopee orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function importOrdersTiktok()
    {
        set_time_limit(0);
        $range = 'Tiktok Processed!A2:S'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedCount = 0;
        $rowIndex = 2; // Starting from A2 in the sheet

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $orderData = [
                    'date'                 => !empty($row[6]) ? Carbon::createFromFormat('d/m/Y H:i:s', $row[6])->format('Y-m-d') : null,
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 4, // Tiktok
                    'customer_name'        => $row[8] ?? null,
                    'customer_phone_number' => $row[9] ?? null,
                    'product'              => $row[2] ?? null,
                    'qty'                  => $row[4] ?? null,
                    'receipt_number'       => $row[15] ?? null, // Column P
                    'shipment'             => $row[14] ?? null, // Column O
                    'payment_method'       => $row[16] ?? null, // Column Q
                    'sku'                  => $row[1] ?? null,
                    'variant'              => $row[3] ?? null,
                    'price'                => $row[5] ?? null,
                    'username'             => $row[7] ?? null,
                    'shipping_address'     => $row[12] ?? null, // Column M
                    'city'                 => $row[11] ?? null, // Column L
                    'province'             => $row[10] ?? null, // Column K
                    'amount'               => $row[18] ?? null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[13] ?? null, // Column N
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tiktok orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_count' => $skippedCount
        ]);
    }
    public function importOrdersTokopedia()
    {
        set_time_limit(0);
        $range = 'Tokopedia Processed!A2:S'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedCount = 0;
        $skippedDueToNullDate = 0;
        $rowIndex = 2; // Starting from A2 in the sheet

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip the row if date is empty or null
                if (empty($row[6])) {
                    $skippedDueToNullDate++;
                    $rowIndex++;
                    continue;
                }

                $orderData = [
                    'date'                 => Carbon::createFromFormat('d/m/Y H:i:s', $row[6])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 3,
                    'customer_name'        => $row[8] ?? null,
                    'customer_phone_number' => $row[9] ?? null,
                    'product'              => $row[2] ?? null,
                    'qty'                  => $row[4] ?? null,
                    'receipt_number'       => $row[15] ?? null, // Column P
                    'shipment'             => $row[14] ?? null, // Column O
                    'payment_method'       => $row[16] ?? null, // Column Q
                    'sku'                  => $row[1] ?? null,
                    'variant'              => $row[3] ?? null,
                    'price'                => $row[5] ?? null,
                    'username'             => $row[7] ?? null,
                    'shipping_address'     => $row[12] ?? null, // Column M
                    'city'                 => $row[11] ?? null, // Column L
                    'province'             => $row[10] ?? null, // Column K
                    'amount'               => $row[18] ?? null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[13] ?? null, // Column N
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tokopedia orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_count' => $skippedCount,
            'skipped_due_to_null_date' => $skippedDueToNullDate
        ]);
    }
    public function importOrdersLazada()
    {
        set_time_limit(0);
        $range = 'Lazada Processed!A2:Q'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $rowIndex = 2; // Starting from A2 in the sheet
        $skippedCount = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Handle price formatting (remove decimal part for bigint)
                $price = !empty($row[8]) ? floatval(str_replace(',', '', $row[8])) : 0;
                
                // Parse date
                $orderDate = !empty($row[3]) ? Carbon::parse($row[3])->format('Y-m-d') : null;
                
                $orderData = [
                    'date'                 => $orderDate,
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 2, // Lazada
                    'customer_name'        => $row[9] ?? null,
                    'customer_phone_number' => $row[16] ?? null, // Column Q
                    'product'              => $row[5] ?? null,
                    'qty'                  => 1,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[15] ?? null, // Column P
                    'payment_method'       => $row[4] ?? null,
                    'sku'                  => $row[6] ?? null,
                    'variant'              => $row[7] ?? null,
                    'price'                => $price,
                    'username'             => $row[9] ?? null,
                    'shipping_address'     => $row[11] ?? null,
                    'city'                 => $row[12] ?? null,
                    'province'             => $row[13] ?? null,
                    'amount'               => $price,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[14] ?? null, // Column O
                ];

                // Only check for duplicates when id_order, sku, and date are all the same
                $existingOrder = Order::where('id_order', $orderData['id_order'])
                    ->where('sku', $orderData['sku'])
                    ->where('date', $orderData['date'])
                    ->first();

                // If no identical order exists (based on id_order, sku, date), create it
                if (!$existingOrder) {
                    // Add timestamps for creation
                    $orderData['created_at'] = now();
                    $orderData['updated_at'] = now();
                    
                    Order::create($orderData);
                    $processedRows++;
                } else {
                    // Update the status of the existing order
                    $existingOrder->status = $orderData['status'];
                    $existingOrder->updated_at = now();
                    $existingOrder->save();
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Lazada orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
        ]);
    }
    public function importAzrinaTiktok()
    {
        set_time_limit(0);
        $range = 'Azrina Tiktok Processed!A2:S'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedCount = 0;
        $rowIndex = 2; // Starting from A2 in the sheet

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $orderData = [
                    'date'                 => !empty($row[6]) ? Carbon::createFromFormat('d/m/Y H:i:s', $row[6])->format('Y-m-d') : null,
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 4, // Tiktok
                    'customer_name'        => $row[8] ?? null,
                    'customer_phone_number' => $row[9] ?? null,
                    'product'              => $row[2] ?? null,
                    'qty'                  => $row[4] ?? null,
                    'receipt_number'       => $row[15] ?? null, // Column P
                    'shipment'             => $row[14] ?? null, // Column O
                    'payment_method'       => $row[16] ?? null, // Column Q
                    'sku'                  => $row[1] ?? null,
                    'variant'              => $row[3] ?? null,
                    'price'                => $row[5] ?? null,
                    'username'             => $row[7] ?? null,
                    'shipping_address'     => $row[12] ?? null, // Column M
                    'city'                 => $row[11] ?? null, // Column L
                    'province'             => $row[10] ?? null, // Column K
                    'amount'               => $row[18] ?? null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[13] ?? null, // Column N
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tiktok orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_count' => $skippedCount
        ]);
    }
    public function importAzrinaLazada()
    {
        set_time_limit(0);
        $range = 'Azrina Lazada Processed!A2:Q'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedCount = 0; 
        $rowIndex = 2;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Handle price formatting (remove decimal part for bigint)
                $price = !empty($row[8]) ? floatval(str_replace(',', '', $row[8])) : 0;
                
                // Parse date
                $orderDate = !empty($row[3]) ? Carbon::parse($row[3])->format('Y-m-d') : null;
                
                $orderData = [
                    'date'                 => $orderDate,
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 2, // Lazada
                    'customer_name'        => $row[9] ?? null,
                    'customer_phone_number' => $row[16] ?? null, // Column Q
                    'product'              => $row[5] ?? null,
                    'qty'                  => 1,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[15] ?? null, // Column P
                    'payment_method'       => $row[4] ?? null,
                    'sku'                  => $row[6] ?? null,
                    'variant'              => $row[7] ?? null,
                    'price'                => $price,
                    'username'             => $row[9] ?? null,
                    'shipping_address'     => $row[11] ?? null,
                    'city'                 => $row[12] ?? null,
                    'province'             => $row[13] ?? null,
                    'amount'               => $price,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[14] ?? null, // Column O
                ];

                // Only check for duplicates when id_order, sku, and date are all the same
                $existingOrder = Order::where('id_order', $orderData['id_order'])
                    ->where('sku', $orderData['sku'])
                    ->where('date', $orderData['date'])
                    ->first();

                // If no identical order exists (based on id_order, sku, date), create it
                if (!$existingOrder) {
                    // Add timestamps for creation
                    $orderData['created_at'] = now();
                    $orderData['updated_at'] = now();
                    
                    Order::create($orderData);
                    $processedRows++;
                } else {
                    // Update the status of the existing order
                    $existingOrder->status = $orderData['status'];
                    $existingOrder->updated_at = now();
                    $existingOrder->save();
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Lazada orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
        ]);
    }
    public function importAzrinaShopee()
    {
        set_time_limit(0);
        $range = 'Azrina Shopee Processed!A2:R'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[3])) {
                    $skippedRows++;
                    continue;
                }
                
                // Parse price by completely removing all dots and commas to store as integer
                $price = null;
                if (isset($row[14])) {
                    // Remove all dots (thousand separators) and any commas
                    $price = preg_replace('/[.,]/', '', $row[14]);
                    // Make sure it's a valid integer
                    $price = is_numeric($price) ? (int)$price : null;
                }
                
                // Parse amount similarly
                $amount = null;
                if (isset($row[14]) && isset($row[15])) {
                    $price_val = preg_replace('/[.,]/', '', $row[14]);
                    $shipping_val = preg_replace('/[.,]/', '', $row[15]);
                    
                    // Only calculate if both are numeric
                    if (is_numeric($price_val) && is_numeric($shipping_val)) {
                        $amount = (int)$price_val + (int)$shipping_val;
                    }
                }
                
                $orderData = [
                    'date'                 => Carbon::parse($row[3])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 1, // Shopee
                    'customer_name'        => $row[7] ?? null,
                    'customer_phone_number' => $row[8] ?? null,
                    'product'              => $row[5] ?? null,
                    'qty'                  => $row[12] ?? null,
                    'receipt_number'       => $row[1] ?? null,
                    'shipment'             => $row[2] ?? null,
                    'payment_method'       => $row[13] ?? null,
                    'sku'                  => $row[4] ?? null,
                    'variant'              => null,
                    'price'                => $price,
                    'username'             => $row[6] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount'               => $amount,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[17] ?? null, // Column R
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->where('amount', $orderData['amount'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->sku = $orderData['sku'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Shopee orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function importAzrinaTokopedia()
    {
        set_time_limit(0);
        $range = 'Azrina Tokopedia Processed!A2:P'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedCount = 0;
        $skippedDueToNullDate = 0;
        $rowIndex = 2; // Starting from A2 in the sheet

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip the row if date is empty or null
                if (empty($row[6])) {
                    $skippedDueToNullDate++;
                    $rowIndex++;
                    continue;
                }

                $orderData = [
                    'date'                 => Carbon::createFromFormat('d/m/Y H:i:s', $row[6])->format('Y-m-d'),
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 3,
                    'customer_name'        => $row[8] ?? null,
                    'customer_phone_number' => $row[9] ?? null,
                    'product'              => $row[2] ?? null,
                    'qty'                  => $row[4] ?? null,
                    'receipt_number'       => $row[15] ?? null, // Column P
                    'shipment'             => $row[14] ?? null, // Column O
                    'payment_method'       => $row[16] ?? null, // Column Q
                    'sku'                  => $row[1] ?? null,
                    'variant'              => $row[3] ?? null,
                    'price'                => $row[5] ?? null,
                    'username'             => $row[7] ?? null,
                    'shipping_address'     => $row[12] ?? null, // Column M
                    'city'                 => $row[11] ?? null, // Column L
                    'province'             => $row[10] ?? null, // Column K
                    'amount'               => $row[18] ?? null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[13] ?? null, // Column N
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->first();

                // If order exists, update the status
                if ($order) {
                    // Only update if the status has changed
                    if ($order->status != $orderData['status']) {
                        $order->status = $orderData['status'];
                        $order->updated_at = now();
                        $order->save();
                        $updatedRows++;
                    } else {
                        $skippedCount++;
                    }
                } else {
                    // If order doesn't exist, create it with created_at timestamp
                    $orderData['created_at'] = now();
                    Order::create($orderData);
                    $processedRows++;
                }
                
                $rowIndex++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tokopedia orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'skipped_count' => $skippedCount,
            'skipped_due_to_null_date' => $skippedDueToNullDate
        ]);
    }

    public function getMonthlyOrderStatusDistribution(): JsonResponse
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $tenantId = Auth::user()->current_tenant_id;

        $orderData = Order::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('status, SUM(amount) as total_amount')
            ->groupBy('status')
            ->get();

        // Calculate total amount for percentage calculation
        $totalAmount = $orderData->sum('total_amount');

        // Prepare data for ChartJS
        $labels = [];
        $data = [];
        $percentages = [];
        $backgroundColor = [
            '#36A2EB',  // blue
            '#FF6384',  // red
            '#4BC0C0',  // turquoise
            '#FF9F40',  // orange
            '#9966FF',  // purple
            '#FFCD56',  // yellow
            '#C9CBCF',  // grey
            '#7BC8A4'   // green
        ];

        foreach ($orderData as $index => $order) {
            $labels[] = ucfirst($order->status);
            $data[] = $order->total_amount;
            
            // Calculate percentage with 2 decimal places
            $percentage = $totalAmount > 0 
                ? round(($order->total_amount / $totalAmount) * 100, 2) 
                : 0;
            $percentages[] = $percentage;
        }

        // Format the response for ChartJS
        $response = [
            'type' => 'pie',
            'data' => [
                'labels' => array_map(function($label, $percentage) {
                    return "$label ($percentage%)";
                }, $labels, $percentages),
                'datasets' => [[
                    'data' => $data,
                    'backgroundColor' => array_slice($backgroundColor, 0, count($data)),
                    'borderWidth' => 1
                ]]
            ],
            'options' => [
                'plugins' => [
                    'legend' => [
                        'position' => 'right'
                    ],
                    'tooltip' => [
                        'callbacks' => [
                            'label' => '
                                function(context) {
                                    var label = context.label || "";
                                    var value = context.raw || 0;
                                    return label + ": Rp " + value.toLocaleString("id-ID");
                                }
                            '
                        ]
                    ]
                ]
            ],
            'rawData' => [
                'labels' => $labels,
                'values' => $data,
                'percentages' => $percentages,
                'totalAmount' => $totalAmount
            ]
        ];

        return response()->json($response);
    }
    
    public function getDailyStatusTrend(): JsonResponse
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();
        $tenantId = Auth::user()->current_tenant_id;

        $orders = Order::where('tenant_id', $tenantId)
            ->whereBetween('date', [$startDate, $endDate])
            ->select('date', 'status', DB::raw('SUM(amount) as total_amount'))
            ->groupBy('date', 'status')
            ->orderBy('date')
            ->get();

        // Get unique dates and statuses
        $dates = $orders->pluck('date')->unique()->sort()->values();
        $statuses = $orders->pluck('status')->unique()->values();

        // Prepare data structure
        $datasets = [];
        $colors = [
            'cancelled' => '#36A2EB',      // blue
            'completed' => '#FF6384',      // red
            'packing' => '#4BC0C0',        // turquoise
            'paid' => '#FF9F40',          // orange
            'process' => '#9966FF',       // purple
            'request_cancel' => '#FFCD56', // yellow
            'sent' => '#C9CBCF',          // grey
            'sent_booking' => '#7BC8A4'    // green
        ];

        // Create dataset for each status
        foreach ($statuses as $status) {
            $data = [];
            foreach ($dates as $date) {
                $amount = $orders->where('date', $date)
                            ->where('status', $status)
                            ->first()->total_amount ?? 0;
                $data[] = [
                    'x' => $date,
                    'y' => $amount
                ];
            }

            $datasets[] = [
                'label' => ucfirst($status),
                'data' => $data,
                'borderColor' => $colors[strtolower($status)] ?? '#' . substr(md5($status), 0, 6),
                'backgroundColor' => ($colors[strtolower($status)] ?? '#' . substr(md5($status), 0, 6)) . '20',
                'tension' => 0.4,
                'fill' => true
            ];
        }

        return response()->json([
            'datasets' => $datasets,
            'dates' => $dates
        ]);
    }

    public function getOrdersBySalesChannel(Request $request)
    {
        $query = Order::select('sales_channels.name', DB::raw('COUNT(orders.id) as count'))
            ->rightJoin('sales_channels', 'orders.sales_channel_id', '=', 'sales_channels.id')
            ->where('orders.tenant_id', Auth::user()->current_tenant_id);

        // Apply date filter if provided in request
        if ($request->filled('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString);
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString);
            
            $query->whereBetween('orders.date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        } else {
            // Default to current month when no date filter is provided
            $startDate = Carbon::now()->startOfMonth();
            $endDate = Carbon::now()->endOfMonth();
            
            $query->whereBetween('orders.date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);
        }

        // Apply process date filter
        // if ($request->filled('filterProcessDates')) {
        //     [$processStartDate, $processEndDate] = explode(' - ', $request->filterProcessDates);
        //     $processStartDate = Carbon::createFromFormat('d/m/Y', $processStartDate);
        //     $processEndDate = Carbon::createFromFormat('d/m/Y', $processEndDate);
            
        //     $query->whereBetween('orders.process_at', [
        //         $processStartDate->format('Y-m-d'),
        //         $processEndDate->format('Y-m-d')
        //     ]);
        // }

        // Apply other filters
        if ($request->filterChannel) {
            $query->where('orders.sales_channel_id', $request->filterChannel);
        }
        
        if ($request->filterStatus) {
            $query->where('orders.status', $request->filterStatus);
        }

        $orderCounts = $query->groupBy('sales_channels.id', 'sales_channels.name')
            ->get();

        // Rest of the code remains the same...
        $labels = $orderCounts->pluck('name')->toArray();
        $data = $orderCounts->pluck('count')->toArray();
        
        $backgroundColors = [
            'Shopee' => '#EE4D2D',
            'Lazada' => '#0F146D',
            'Tokopedia' => '#42B549',
            'Tiktok Shop' => '#000000',
            'Reseller' => '#FF6B6B',
            'Others' => '#6C757D',
        ];

        $colors = $orderCounts->map(function($item) use ($backgroundColors) {
            return $backgroundColors[$item->name] ?? '#6C757D';
        })->toArray();

        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => $colors,
                    'hoverBackgroundColor' => $colors,
                    'borderWidth' => 0
                ]
            ]
        ]);
    }

    public function getDailyOrdersByChannel(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        // Override default dates if filterDates is provided
        if ($request->filled('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString);
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString);
        }

        $channelColors = [
            'Shopee' => '#EE4D2D',
            'Lazada' => '#0F146D',
            'Tokopedia' => '#42B549',
            'Tiktok Shop' => '#000000',
            'Reseller' => '#FF6B6B',
            'Others' => '#6C757D',
        ];

        $orderCounts = SalesChannel::select(
                'sales_channels.name',
                'orders.date',
                Order::raw('COUNT(orders.id) as count')
            )
            ->leftJoin('orders', function($join) use ($startDate, $endDate, $request) {
                $join->on('orders.sales_channel_id', '=', 'sales_channels.id')
                    ->whereNotNull('orders.date')
                    ->where('orders.tenant_id', Auth::user()->current_tenant_id)
                    ->whereBetween('orders.date', [
                        $startDate->format('Y-m-d'),
                        $endDate->format('Y-m-d')
                    ]);

                // Apply process_at date filter if provided
                // if ($request->filled('filterProcessDates')) {
                //     [$processStartDate, $processEndDate] = explode(' - ', $request->filterProcessDates);
                //     $processStartDate = Carbon::createFromFormat('d/m/Y', $processStartDate);
                //     $processEndDate = Carbon::createFromFormat('d/m/Y', $processEndDate);
                    
                //     $join->whereBetween('orders.process_at', [
                //         $processStartDate->format('Y-m-d'),
                //         $processEndDate->format('Y-m-d')
                //     ]);
                // }

                // Apply other filters in the join
                if ($request->filterChannel) {
                    $join->where('orders.sales_channel_id', $request->filterChannel);
                }
                
                if ($request->filterStatus) {
                    $join->where('orders.status', $request->filterStatus);
                }
            })
            ->groupBy('sales_channels.name', 'orders.date')
            ->having(Order::raw('COUNT(orders.id)'), '>', 0)
            ->orderBy('orders.date')
            ->get();

        // Rest of the code remains the same...
        $groupedCounts = $orderCounts->groupBy('name');

        $datasets = [];
        foreach ($groupedCounts as $channelName => $channelData) {
            $dataset = [
                'label' => $channelName,
                'data' => [],
                'borderColor' => $channelColors[$channelName] ?? '#6C757D',
                'backgroundColor' => ($channelColors[$channelName] ?? '#6C757D') . '20',
                'tension' => 0.4,
                'fill' => true
            ];

            foreach ($channelData as $data) {
                $dataset['data'][] = [
                    'x' => $data->date,
                    'y' => (int)$data->count
                ];
            }

            if (!empty($dataset['data'])) {
                $datasets[] = $dataset;
            }
        }

        $dates = $orderCounts->pluck('date')->unique()->sort()->values()->toArray();

        return response()->json([
            'datasets' => $datasets,
            'dates' => $dates
        ]);
    }

    public function getDailyQuantityBySku(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($request->filled('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString);
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString);
        }

        $tenant_id = Auth::user()->current_tenant_id;

        $dailyHppData = DailyHpp::select(
                'daily_hpp.date',
                DB::raw('SUM(daily_hpp.quantity) as total_quantity')
            )
            ->where('daily_hpp.tenant_id', $tenant_id)
            ->whereBetween('daily_hpp.date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);

        if ($request->filterChannel) {
            $dailyHppData->where('daily_hpp.sales_channel_id', $request->filterChannel);
        }
        
        $dailyHppData = $dailyHppData->groupBy('daily_hpp.date')
            ->orderBy('daily_hpp.date')
            ->get();

        $dataset = [
            'label' => 'Total Quantity',
            'data' => [],
            'borderColor' => '#4361EE',
            'backgroundColor' => '#4361EE20',
            'tension' => 0.4,
            'fill' => true
        ];

        foreach ($dailyHppData as $data) {
            $formattedDate = Carbon::parse($data->date)->format('Y-m-d\TH:i:s.000\Z');
            $dataset['data'][] = [
                'x' => $formattedDate, // Use ISO format for consistency
                'y' => (int)$data->total_quantity
            ];
        }

        $dates = $dailyHppData->pluck('date')->unique()->sort()->values()->toArray();

        return response()->json([
            'datasets' => [$dataset],
            'dates' => $dates
        ]);
    }

    public function getQuantityBySku(Request $request)
    {
        $startDate = Carbon::now()->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        if ($request->filled('filterDates')) {
            [$startDateString, $endDateString] = explode(' - ', $request->filterDates);
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString);
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString);
        }

        $tenant_id = Auth::user()->current_tenant_id;

        $skuCounts = DailyHpp::select(
                'daily_hpp.sku',
                DB::raw('SUM(daily_hpp.quantity) as total_quantity')
            )
            ->where('daily_hpp.tenant_id', $tenant_id)
            ->whereBetween('daily_hpp.date', [
                $startDate->format('Y-m-d'),
                $endDate->format('Y-m-d')
            ]);

        if ($request->filterChannel) {
            $skuCounts->where('daily_hpp.sales_channel_id', $request->filterChannel);
        }
        
        $skuCounts = $skuCounts->groupBy('daily_hpp.sku')
            ->orderBy('total_quantity', 'desc')
            ->limit(10)
            ->get();

        $colors = [
            '#FF6384', '#36A2EB', '#FFCE56', '#4BC0C0', '#9966FF',
            '#FF9F40', '#C9CBCF', '#7BC8A4', '#E7E9ED', '#1B9E77'
        ];

        $labels = $skuCounts->pluck('sku')->toArray();
        $data = $skuCounts->pluck('total_quantity')->toArray();
        
        return response()->json([
            'labels' => $labels,
            'datasets' => [
                [
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($labels)),
                    'hoverBackgroundColor' => array_slice($colors, 0, count($labels)),
                    'borderWidth' => 0
                ]
            ]
        ]);
    }

    public function getSkuQuantities(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $skuCounts = [];
        
        DB::table('orders')
            ->select('sku')
            ->whereDate('date', $date)
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

        arsort($skuCounts);
        
        $data = [];
        foreach ($skuCounts as $sku => $quantity) {
            $data[] = [
                "sku" => $sku,
                "quantity" => $quantity
            ];
        }
        
        return response()->json(['data' => $data]);
    }

    public function exportSkuQuantities(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        return (new SkuQuantitiesExport($date))->download('sku-quantities-' . $date . '.xlsx');
    }
    public function skuQuantities() {
        return view('admin.order.qty_sku');
    }
    public function getSkuDetail(Request $request)
    {
        $sku = $request->input('sku');
        $date = $request->input('date');

        $orders = DB::table('orders')
            ->select('id_order', 'date', 'customer_name', 'sku', 'qty', 'status')  // Added status
            ->whereDate('date', $date)
            ->where(function($query) use ($sku) {
                $query->where('sku', 'like', '%' . $sku . '%')
                    ->orWhere('sku', 'like', '%' . $sku)
                    ->orWhere('sku', 'like', $sku . '%');
            })
            ->get();

        $detailedOrders = [];
        foreach ($orders as $order) {
            $skuItems = explode(',', $order->sku);
            foreach ($skuItems as $item) {
                $item = trim($item);
                if (strpos($item, $sku) !== false) {
                    if (preg_match('/^(\d+)\s+(.+)$/', $item, $matches)) {
                        $qty = (int)$matches[1];
                    } else {
                        $qty = 1;
                    }
                    
                    $detailedOrders[] = [
                        'id_order' => $order->id_order,
                        'date' => $order->date,
                        'customer_name' => $order->customer_name,
                        'sku' => $sku,
                        'qty' => $qty,
                        'status' => $order->status  // Added status
                    ];
                }
            }
        }

        return response()->json(['data' => $detailedOrders]);
    }

    public function getHPP(Request $request)
    {
        $date = $request->input('date', today()->format('Y-m-d'));
        $skuCounts = [];
        
        DB::table('orders')
            ->select('sku')
            ->whereDate('date', $date)
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

        arsort($skuCounts);
        
        $data = [];
        foreach ($skuCounts as $sku => $quantity) {
            $product = DB::table('products')
                ->select('harga_satuan')
                ->where('sku', $sku)
                ->first();
                
            $harga_satuan = $product ? $product->harga_satuan : null;
            $hpp = $harga_satuan ? $harga_satuan * $quantity : 0;
            
            $data[] = [
                "sku" => $sku,
                "quantity" => $quantity,
                "harga_satuan" => $harga_satuan,
                "hpp" => $hpp
            ];
        }
        
        return response()->json(['data' => $data]);
    }
    /**
     * Generate daily HPP (Cost of Goods Sold) report
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function generateDailyHpp()
    {
        try {
            $tenantId = Auth::user()->current_tenant_id;
            $startDate = Carbon::now()->startOfMonth()->format('Y-m-d');
            $endDate = Carbon::now()->format('Y-m-d');
            
            // Get orders with their products grouped by date, sku, and sales channel
            $orders = Order::select(
                    'orders.date',
                    'orders.sku',
                    DB::raw('SUM(orders.qty) as quantity'), // Changed to SUM(qty) instead of COUNT
                    'products.harga_satuan as hpp',
                    DB::raw("$tenantId as tenant_id"),
                    'orders.sales_channel_id',
                    DB::raw('NOW() as created_at'),
                    DB::raw('NOW() as updated_at')
                )
                ->leftJoin('products', 'orders.sku', '=', 'products.sku')
                ->where('orders.tenant_id', $tenantId)
                ->whereBetween('orders.date', [$startDate, $endDate])
                ->whereNotIn('orders.status', [
                    'pending', 
                    'cancelled', 
                    'request_cancel', 
                    'request_return',
                    'Batal', 
                    'Canceled', 
                    'Pembatalan diajukan', 
                    'Dibatalkan Sistem'
                ])
                ->groupBy(
                    'orders.date',
                    'orders.sku',
                    'products.harga_satuan',
                    'orders.sales_channel_id'
                )
                ->get();
                
            // Insert the records into daily_hpp table
            foreach ($orders as $order) {
                $formattedDate = Carbon::parse($order->date)->format('Y-m-d');
                DailyHpp::updateOrCreate(
                    [
                        'date' => $formattedDate,
                        'sku' => $order->sku,
                        'sales_channel_id' => $order->sales_channel_id,
                        'tenant_id' => $tenantId
                    ],
                    [
                        'quantity' => $order->quantity,
                        'HPP' => $order->hpp,
                        'updated_at' => now()
                    ]
                );
            }
            
            return response()->json([
                'status' => 'success',
                'message' => 'Daily HPP data has been generated successfully',
                'start_date' => $startDate,
                'end_date' => $endDate,
                'records_processed' => $orders->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to generate Daily HPP data: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getDailyHPP(Request $request)
    {
        $startDate = now()->startOfMonth();
        $endDate = now();
        $dailyHPP = [];
        
        for ($date = clone $startDate; $date <= $endDate; $date->addDay()) {
            $dailyHPP[$date->format('Y-m-d')] = 0;
        }

        // Process each day
        foreach ($dailyHPP as $date => $total) {
            $skuCounts = [];
            
            DB::table('orders')
                ->select('sku')
                ->whereDate('date', $date)
                ->whereNotIn('status', ['pending', 'cancelled', 'request_cancel', 'request_return', 'Batal', 'Canceled', 'canceled'])
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
            $dailyTotal = 0;
            foreach ($skuCounts as $sku => $quantity) {
                $product = DB::table('products')
                    ->select('harga_satuan')
                    ->where('sku', $sku)
                    ->first();
                    
                $harga_satuan = $product ? $product->harga_satuan : null;
                $hpp = $harga_satuan ? $harga_satuan * $quantity : 0;
                
                $dailyTotal += $hpp;
            }
            
            $dailyHPP[$date] = $dailyTotal;
        }

        // Format the response
        $data = [];
        foreach ($dailyHPP as $date => $total_hpp) {
            $data[] = [
                "date" => $date,
                "total_hpp" => $total_hpp
            ];
        }
        
        return response()->json(['data' => $data]);
    }
    public function getHPPChannel(Request $request)
    {
        $query = DailyHpp::query()
            ->select(
                'daily_hpp.date',
                'sales_channels.name as channel_name',
                'daily_hpp.sku',
                'daily_hpp.quantity',
                'daily_hpp.HPP',
                DB::raw('daily_hpp.quantity * daily_hpp.HPP as total_hpp')
            )
            ->leftJoin('sales_channels', 'daily_hpp.sales_channel_id', '=', 'sales_channels.id')
            ->where('daily_hpp.tenant_id', Auth::user()->current_tenant_id)
            ->whereNotNull('daily_hpp.HPP');
        
        if ($request->filterChannel) {
            $query->where('daily_hpp.sales_channel_id', $request->filterChannel);
        }
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
            $query->whereBetween('daily_hpp.date', [$startDate, $endDate]);
        } else {
            $query->whereMonth('daily_hpp.date', Carbon::now()->month)
                ->whereYear('daily_hpp.date', Carbon::now()->year);
        }
        
        return DataTables::of($query)
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d');
            })
            ->editColumn('HPP', function ($row) {
                return 'Rp ' . number_format($row->HPP, 0, ',', '.');
            })
            ->editColumn('total_hpp', function ($row) {
                return 'Rp ' . number_format($row->total_hpp, 0, ',', '.');
            })
            ->editColumn('quantity', function ($row) {
                return number_format($row->quantity, 0, ',', '.');
            })
            ->make(true);
    }
    public function getHPPSummary(Request $request)
    {
        $query = DailyHpp::query()
            ->where('tenant_id', Auth::user()->current_tenant_id);
        
        // Apply date filter if provided
        if (!is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
            $query->whereBetween('date', [$startDate, $endDate]);
        } else {
            // Default to current month
            $query->whereMonth('date', Carbon::now()->month)
                ->whereYear('date', Carbon::now()->year);
        }

        // Apply sales channel filter if provided
        if ($request->filterChannel) {
            $query->where('sales_channel_id', $request->filterChannel);
        }

        $data = $query->selectRaw('
            SUM(quantity * HPP) as total_hpp,
            SUM(quantity) as total_qty,
            COUNT(DISTINCT CONCAT(date, sales_channel_id, sku)) as sku_count
        ')
        ->first();

        return response()->json([
            'total_hpp' => $data->total_hpp ?? 0,
            'total_qty' => $data->total_qty ?? 0,
            'sku_count' => $data->sku_count ?? 0
        ]);
    }
    public function importCleoraB2B()
    {
        $this->googleSheetService->setSpreadsheetId('1bqiRz8rHFYjLyu9wN1CTDEt9nzeez0dzthPkoJSBUDI');
        $range = 'B2B Cleora!A3:H';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $orderCountMap = [];

        // Add arrays to store details about skipped rows
        $skippedRowsDetails = [];
        $duplicateRowsDetails = [];

        foreach (array_chunk($sheetData, $chunkSize) as $chunkIndex => $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Calculate the actual row number in the spreadsheet (adding 3 because data starts at A3)
                $actualRowNumber = $chunkIndex * $chunkSize + $rowIndex + 3;
                
                // Skip if essential data is missing
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    $skippedRows++;
                    $skippedRowsDetails[] = [
                        'row' => $actualRowNumber,
                        'reason' => 'Missing essential data (date, employee name, or product)',
                        'data' => $row
                    ];
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                try {
                    // Properly handle DD/MM/YYYY format
                    $dateStr = $row[0];
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                        // Format is DD/MM/YYYY
                        $day = $matches[1];
                        $month = $matches[2];
                        $year = $matches[3];
                        $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                    } else {
                        // Try standard parsing as fallback
                        $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $skippedRows++;
                    $skippedRowsDetails[] = [
                        'row' => $actualRowNumber,
                        'reason' => 'Invalid date format: ' . $row[0] . ' - ' . $e->getMessage(),
                        'data' => $row
                    ];
                    continue; // Skip if date is invalid
                }
                
                // Process employee ID (Column B)
                $employeeId = '';
                if (trim($row[1]) == 'Fauzhi') {
                    $employeeId = 'CLEOAZ104';
                } elseif (trim($row[1]) == 'Anisa') {
                    $employeeId = 'CLEOAZ103';
                } else {
                    $skippedRows++;
                    $skippedRowsDetails[] = [
                        'row' => $actualRowNumber,
                        'reason' => 'Unrecognized employee name: ' . $row[1],
                        'data' => $row
                    ];
                    continue; // Skip if employee name is not recognized
                }
                
                // Parse amount
                $amount = $this->parseAmount($row[6] ?? null);
                
                // Check for duplicates based on the combination of fields from the sheet
                $existingOrder = Order::where('date', $orderDate)
                                ->where('product', $row[2])
                                ->where('sku', $row[3])
                                ->where('price', $row[4])
                                ->where('qty', $row[5])
                                ->where('amount', $amount)
                                ->where('customer_name', $row[7])
                                ->where('tenant_id', $tenant_id)
                                ->first();

                if ($existingOrder) {
                    $duplicateRows++;
                    $duplicateRowsDetails[] = [
                        'row' => $actualRowNumber,
                        'reason' => 'Duplicate entry: Order already exists with same details',
                        'data' => $row,
                        'existing_order_id' => $existingOrder->id_order
                    ];
                    continue;
                }
                
                // Generate order number
                $month = Carbon::parse($orderDate)->format('m');
                $year = Carbon::parse($orderDate)->format('y');
                $monthYearKey = $month . $year . $employeeId;
                
                if (!isset($orderCountMap[$monthYearKey])) {
                    // Check if there are existing orders in the database for this month/year/employee
                    $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                    
                    if ($lastOrder) {
                        $parts = explode('/', $lastOrder->id_order);
                        $lastOrderNumber = (int)end($parts);
                        $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                    } else {
                        $orderCountMap[$monthYearKey] = 1;
                    }
                } else {
                    $orderCountMap[$monthYearKey]++;
                }
                
                $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                
                // Generate receipt number (same as id_order)
                $receiptNumber = $generatedIdOrder;
                
                $orderData = [
                    'date'                  => $orderDate,
                    'process_at'            => null,
                    'id_order'              => $generatedIdOrder,
                    'sales_channel_id'      => 5, // As specified
                    'customer_name'         => $row[7] ?? null,
                    'customer_phone_number' => null,
                    'product'               => $row[2] ?? null,
                    'qty'                   => $row[5] ?? null,
                    'receipt_number'        => $receiptNumber,
                    'shipment'              => '-',
                    'payment_method'        => null,
                    'sku'                   => $row[3] ?? null,
                    'variant'               => null,
                    'price'                 => $row[4] ?? null,
                    'username'              => $row[7] ?? null, // Same as customer_name
                    'shipping_address'      => $row[7] ?? null, // Same as customer_name
                    'city'                  => null,
                    'province'              => null,
                    'amount'                => $amount,
                    'tenant_id'             => $tenant_id,
                    'is_booking'            => 0,
                    'status'                => 'reported',
                    'updated_at'            => now(),
                    'created_at'            => now(),
                ];

                // Create new order
                Order::create($orderData);
                $processedRows++;
            }
            usleep(100000);
        }

        // Generate detailed log of skipped rows
        $skippedRowsLog = [];
        foreach ($skippedRowsDetails as $detail) {
            $rowData = array_map(function($value) {
                return $value ?? 'NULL'; 
            }, $detail['data']);
            
            $skippedRowsLog[] = [
                'row_number' => $detail['row'],
                'reason' => $detail['reason'],
                'date' => $rowData[0] ?? 'NULL',
                'employee' => $rowData[1] ?? 'NULL',
                'product' => $rowData[2] ?? 'NULL',
                'sku' => $rowData[3] ?? 'NULL',
                'price' => $rowData[4] ?? 'NULL',
                'qty' => $rowData[5] ?? 'NULL',
                'amount' => $rowData[6] ?? 'NULL',
                'customer' => $rowData[7] ?? 'NULL'
            ];
        }

        // Generate detailed log of duplicate rows
        $duplicateRowsLog = [];
        foreach ($duplicateRowsDetails as $detail) {
            $rowData = array_map(function($value) {
                return $value ?? 'NULL'; 
            }, $detail['data']);
            
            $duplicateRowsLog[] = [
                'row_number' => $detail['row'],
                'reason' => $detail['reason'],
                'existing_order_id' => $detail['existing_order_id'],
                'date' => $rowData[0] ?? 'NULL',
                'employee' => $rowData[1] ?? 'NULL',
                'product' => $rowData[2] ?? 'NULL',
                'sku' => $rowData[3] ?? 'NULL',
                'price' => $rowData[4] ?? 'NULL',
                'qty' => $rowData[5] ?? 'NULL',
                'amount' => $rowData[6] ?? 'NULL',
                'customer' => $rowData[7] ?? 'NULL'
            ];
        }

        return response()->json([
            'message' => 'B2B Cleora orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows,
            'skipped_rows_details' => $skippedRowsLog,
            'duplicate_rows_details' => $duplicateRowsLog
        ]);
    }
    public function importAzrinaB2B()
    {
        $this->googleSheetService->setSpreadsheetId('1bqiRz8rHFYjLyu9wN1CTDEt9nzeez0dzthPkoJSBUDI');
        $range = 'B2B Azrina!A3:H';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 2;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0; // Track duplicates separately
        $orderCountMap = []; // To keep track of order numbers per employee per month

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip if essential data is missing
                if (empty($row[0]) || empty($row[1]) || empty($row[2])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                try {
                    // Properly handle DD/MM/YYYY format
                    $dateStr = $row[0];
                    if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                        // Format is DD/MM/YYYY
                        $day = $matches[1];
                        $month = $matches[2];
                        $year = $matches[3];
                        $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                    } else {
                        // Try standard parsing as fallback
                        $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                    }
                } catch (\Exception $e) {
                    $skippedRows++;
                    continue; // Skip if date is invalid
                }
                
                // Process employee ID (Column B)
                $employeeId = '';
                if (trim($row[1]) == 'Fauzhi') {
                    $employeeId = 'CLEOAZ104';
                } elseif (trim($row[1]) == 'Anisa') {
                    $employeeId = 'CLEOAZ103';
                } else {
                    $skippedRows++;
                    continue; // Skip if employee name is not recognized
                }
                
                // Parse amount
                $amount = $this->parseAmount($row[6] ?? null);
                
                // Check for duplicates based on the combination of fields from the sheet
                $existingOrder = Order::where('date', $orderDate)
                                ->where('product', $row[2])
                                ->where('sku', $row[3])
                                ->where('price', $row[4])
                                ->where('qty', $row[5])
                                ->where('amount', $amount)
                                ->where('customer_name', $row[7])
                                ->where('tenant_id', $tenant_id)
                                ->first();

                if ($existingOrder) {
                    $duplicateRows++;
                    continue;
                }
                
                // Generate order number
                $month = Carbon::parse($orderDate)->format('m');
                $year = Carbon::parse($orderDate)->format('y');
                $monthYearKey = $month . $year . $employeeId;
                
                if (!isset($orderCountMap[$monthYearKey])) {
                    // Check if there are existing orders in the database for this month/year/employee
                    $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                    
                    if ($lastOrder) {
                        // Extract the order number from the last order
                        $parts = explode('/', $lastOrder->id_order);
                        $lastOrderNumber = (int)end($parts);
                        $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                    } else {
                        $orderCountMap[$monthYearKey] = 1;
                    }
                } else {
                    $orderCountMap[$monthYearKey]++;
                }
                
                $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                
                // Generate receipt number (same as id_order)
                $receiptNumber = $generatedIdOrder;
                
                $orderData = [
                    'date'                  => $orderDate,
                    'process_at'            => null,
                    'id_order'              => $generatedIdOrder,
                    'sales_channel_id'      => 5, // As specified
                    'customer_name'         => $row[7] ?? null,
                    'customer_phone_number' => null,
                    'product'               => $row[2] ?? null,
                    'qty'                   => $row[5] ?? null,
                    'receipt_number'        => $receiptNumber,
                    'shipment'              => '-',
                    'payment_method'        => null,
                    'sku'                   => $row[3] ?? null,
                    'variant'               => null,
                    'price'                 => $row[4] ?? null,
                    'username'              => $row[7] ?? null, // Same as customer_name
                    'shipping_address'      => $row[7] ?? null, // Same as customer_name
                    'city'                  => null,
                    'province'              => null,
                    'amount'                => $amount,
                    'tenant_id'             => $tenant_id,
                    'is_booking'            => 0,
                    'status'                => 'reported',
                    'updated_at'            => now(),
                    'created_at'            => now(),
                ];

                // Create new order
                Order::create($orderData);
                $processedRows++;
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'B2B Azrina orders imported successfully',
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }


    /**
     * Parse amount values from various formats to integer
     * 
     * @param string|null $amount
     * @return int|null
     */
    private function parseAmount($amount)
    {
        if (empty($amount)) {
            return null;
        }
        
        // Remove currency symbol (Rp) if present
        $amount = str_replace('Rp', '', $amount);
        
        // Remove all dots, commas, and spaces
        $amount = str_replace(['.', ',', ' '], '', $amount);
        
        // If the amount is still not numeric, return null
        if (!is_numeric($amount)) {
            return null;
        }
        
        // Convert to integer (bigint in MySQL)
        return (int) $amount;
    }
    
    public function importClosingAnisa()
    {
        $this->googleSheetService->setSpreadsheetId('1hMubpvYFyDnPJB3NtiOwH-nH0Qwb9wz7Sq4laVESvPM');
        $range = 'Closing Anisa!A:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $orderCountMap = [];
        
        // Initialize variables to track the last valid values for columns A-D
        $lastOrderDate = null;
        $lastCustomerName = null;
        $lastShippingAddress = null;
        $lastPhoneNumber = null;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if no product (Column E) or amount (Column H) is provided
                if (empty($row[4]) || empty($row[7])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                
                if (!empty($row[0])) {
                    try {
                        $dateStr = $row[0];
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                            $day = $matches[1];
                            $month = $matches[2];
                            $year = $matches[3];
                            $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                            $lastOrderDate = $orderDate; // Update the last valid date
                        } else {
                            $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                            $lastOrderDate = $orderDate; // Update the last valid date
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, use the last valid date if available
                        $orderDate = $lastOrderDate;
                        if (empty($orderDate)) {
                            $skippedRows++;
                            continue; // Skip if no valid date can be determined
                        }
                    }
                } else {
                    // If no date provided, use the last valid date if available
                    $orderDate = $lastOrderDate;
                    if (empty($orderDate)) {
                        $skippedRows++;
                        continue; // Skip if no valid date can be determined
                    }
                }
                
                // Process customer name (Column B)
                if (!empty($row[1])) {
                    $lastCustomerName = $row[1]; // Update the last valid customer name
                }
                
                // Process shipping address (Column C)
                if (!empty($row[2])) {
                    $lastShippingAddress = $row[2]; // Update the last valid shipping address
                }
                
                // Process phone number (Column D)
                if (!empty($row[3])) {
                    // Remove non-digit characters
                    $cleanedPhone = preg_replace('/\D/', '', $row[3]);
                    
                    // Ensure it starts with 0
                    if (!empty($cleanedPhone) && substr($cleanedPhone, 0, 1) !== '0') {
                        $cleanedPhone = '0' . $cleanedPhone;
                    }
                    
                    $lastPhoneNumber = $cleanedPhone; // Update the last valid phone number
                }
                
                // Skip if we still don't have a customer name
                if (empty($lastCustomerName)) {
                    $skippedRows++;
                    continue;
                }
                
                // Process SKUs (Column F)
                $skus = [];
                if (!empty($row[5])) {
                    // Split SKUs by comma and trim whitespace
                    $skus = array_map('trim', explode(',', $row[5]));
                } else {
                    $skus = [null]; // Create at least one row even if SKU is missing
                }
                
                // Parse the amount/price from Column H
                $amount = $this->parseAmount($row[7]);
                
                // Double-check that amount isn't null after parsing
                if ($amount === null) {
                    $skippedRows++;
                    continue;
                }
                
                // Create a separate order entry for each SKU
                foreach ($skus as $sku) {
                    // Generate order number
                    $month = Carbon::parse($orderDate)->format('m');
                    $year = Carbon::parse($orderDate)->format('y');
                    $employeeId = 'CLEOAZ110'; // As specified in requirements
                    $monthYearKey = $month . $year . $employeeId;
                    
                    if (!isset($orderCountMap[$monthYearKey])) {
                        // Check if there are existing orders in the database for this month/year/employee
                        $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                        
                        if ($lastOrder) {
                            // Extract the order number from the last order
                            $parts = explode('/', $lastOrder->id_order);
                            $lastOrderNumber = (int)end($parts);
                            $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                        } else {
                            $orderCountMap[$monthYearKey] = 1;
                        }
                    } else {
                        $orderCountMap[$monthYearKey]++;
                    }
                    
                    $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                    $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                    
                    // Check for duplicates based on the combination of fields from the sheet
                    $existingOrder = Order::where('date', $orderDate)
                                    ->where('product', $row[4] ?? null)
                                    ->where('sku', $sku)
                                    ->where('qty', $row[6] ?? null)
                                    ->where('amount', $amount)
                                    ->where('customer_name', $lastCustomerName)
                                    ->where('tenant_id', $tenant_id)
                                    ->first();

                    if ($existingOrder) {
                        // Skip this row as it already exists
                        $duplicateRows++;
                        continue;
                    }
                    
                    $orderData = [
                        'date'                  => $orderDate,
                        'process_at'            => null,
                        'id_order'              => $generatedIdOrder,
                        'sales_channel_id'      => 10, // As specified
                        'customer_name'         => $lastCustomerName,
                        'customer_phone_number' => $lastPhoneNumber,
                        'product'               => $row[4] ?? null,
                        'qty'                   => $row[6] ?? null,
                        'receipt_number'        => "-",
                        'shipment'              => "-",
                        'payment_method'        => $row[8] ?? null,
                        'sku'                   => $sku,
                        'variant'               => null,
                        'price'                 => $amount,
                        'username'              => $lastCustomerName, // Same as customer_name
                        'shipping_address'      => $lastShippingAddress,
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $amount,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => 'reported',
                        'updated_at'            => now(),
                        'created_at'            => now(),
                    ];

                    // Create new order
                    Order::create($orderData);
                    $processedRows++;
                }
                
                usleep(100000); // Small delay to prevent overwhelming the server
            }
        }

        return response()->json([
            'message' => 'Closing Anisa orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }
    public function importClosingRina()
    {
        $this->googleSheetService->setSpreadsheetId('1hMubpvYFyDnPJB3NtiOwH-nH0Qwb9wz7Sq4laVESvPM');
        $range = 'Closing Rina!A:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $notCurrentMonthRows = 0;
        $orderCountMap = [];
        
        // Get current month and year for filtering
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        // Initialize variables to track the last valid values for columns A-D
        $lastOrderDate = null;
        $lastCustomerName = null;
        $lastShippingAddress = null;
        $lastPhoneNumber = null;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if no product (Column E) or amount (Column H) is provided
                if (empty($row[4]) || empty($row[7])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                
                if (!empty($row[0])) {
                    try {
                        $dateStr = $row[0];
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                            $day = $matches[1];
                            $month = $matches[2];
                            $year = $matches[3];
                            $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                            $lastOrderDate = $orderDate; // Update the last valid date
                        } else {
                            $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                            $lastOrderDate = $orderDate; // Update the last valid date
                        }
                        
                        // Check if the order date is in the current month
                        $orderMonthYear = Carbon::parse($orderDate);
                        if ($orderMonthYear->format('m') != $currentMonth || $orderMonthYear->format('Y') != $currentYear) {
                            $notCurrentMonthRows++;
                            continue; // Skip if not in current month and year
                        }
                        
                    } catch (\Exception $e) {
                        // If date parsing fails, use the last valid date if available
                        $orderDate = $lastOrderDate;
                        if (empty($orderDate)) {
                            $skippedRows++;
                            continue; // Skip if no valid date can be determined
                        }
                        
                        // Check if the last valid date is in the current month
                        $orderMonthYear = Carbon::parse($orderDate);
                        if ($orderMonthYear->format('m') != $currentMonth || $orderMonthYear->format('Y') != $currentYear) {
                            $notCurrentMonthRows++;
                            continue; // Skip if not in current month and year
                        }
                    }
                } else {
                    // If no date provided, use the last valid date if available
                    $orderDate = $lastOrderDate;
                    if (empty($orderDate)) {
                        $skippedRows++;
                        continue; // Skip if no valid date can be determined
                    }
                    
                    // Check if the last valid date is in the current month
                    $orderMonthYear = Carbon::parse($orderDate);
                    if ($orderMonthYear->format('m') != $currentMonth || $orderMonthYear->format('Y') != $currentYear) {
                        $notCurrentMonthRows++;
                        continue; // Skip if not in current month and year
                    }
                }
                
                // Process customer name (Column B)
                if (!empty($row[1])) {
                    $lastCustomerName = $row[1]; // Update the last valid customer name
                }
                
                // Process shipping address (Column C)
                if (!empty($row[2])) {
                    $lastShippingAddress = $row[2]; // Update the last valid shipping address
                }
                
                // Process phone number (Column D)
                if (!empty($row[3])) {
                    // Remove non-digit characters
                    $cleanedPhone = preg_replace('/\D/', '', $row[3]);
                    
                    // Ensure it starts with 0
                    if (!empty($cleanedPhone) && substr($cleanedPhone, 0, 1) !== '0') {
                        $cleanedPhone = '0' . $cleanedPhone;
                    }
                    
                    $lastPhoneNumber = $cleanedPhone; // Update the last valid phone number
                }
                
                // Skip if we still don't have a customer name
                if (empty($lastCustomerName)) {
                    $skippedRows++;
                    continue;
                }
                
                // Process SKUs (Column F)
                $skus = [];
                if (!empty($row[5])) {
                    // Split SKUs by comma and trim whitespace
                    $skus = array_map('trim', explode(',', $row[5]));
                } else {
                    $skus = [null]; // Create at least one row even if SKU is missing
                }
                
                // Parse the amount/price from Column H
                $amount = $this->parseAmount($row[7]);
                
                // Double-check that amount isn't null after parsing
                if ($amount === null) {
                    $skippedRows++;
                    continue;
                }
                
                // Create a separate order entry for each SKU
                foreach ($skus as $index => $sku) {
                    // Generate order number
                    $month = Carbon::parse($orderDate)->format('m');
                    $year = Carbon::parse($orderDate)->format('y');
                    $employeeId = 'CLEOAZ114'; // As specified in requirements
                    $monthYearKey = $month . $year . $employeeId;
                    
                    if (!isset($orderCountMap[$monthYearKey])) {
                        // Check if there are existing orders in the database for this month/year/employee
                        $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                        
                        if ($lastOrder) {
                            // Extract the order number from the last order
                            $parts = explode('/', $lastOrder->id_order);
                            $lastOrderNumber = (int)end($parts);
                            $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                        } else {
                            $orderCountMap[$monthYearKey] = 1;
                        }
                    } else {
                        $orderCountMap[$monthYearKey]++;
                    }
                    
                    $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                    $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                    
                    // Only assign the amount to the first SKU, set 0 for others
                    $orderAmount = ($index === 0) ? $amount : 0;
                    $orderPrice = ($index === 0) ? $amount : 0;
                    
                    // Check for duplicates based on the combination of fields from the sheet
                    $existingOrder = Order::where('date', $orderDate)
                                    ->where('product', $row[4] ?? null)
                                    ->where('sku', $sku)
                                    ->where('qty', $row[6] ?? null)
                                    ->where('amount', $orderAmount)
                                    ->where('customer_name', $lastCustomerName)
                                    ->where('tenant_id', $tenant_id)
                                    ->first();

                    if ($existingOrder) {
                        // Skip this row as it already exists
                        $duplicateRows++;
                        continue;
                    }
                    
                    $orderData = [
                        'date'                  => $orderDate,
                        'process_at'            => null,
                        'id_order'              => $generatedIdOrder,
                        'sales_channel_id'      => 10, // As specified
                        'customer_name'         => $lastCustomerName,
                        'customer_phone_number' => $lastPhoneNumber,
                        'product'               => $row[4] ?? null,
                        'qty'                   => $row[6] ?? null,
                        'receipt_number'        => "-",
                        'shipment'              => "-",
                        'payment_method'        => $row[8] ?? null,
                        'sku'                   => $sku,
                        'variant'               => null,
                        'price'                 => $orderPrice,
                        'username'              => $lastCustomerName, // Same as customer_name
                        'shipping_address'      => $lastShippingAddress,
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $orderAmount,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => 'reported',
                        'updated_at'            => now(),
                        'created_at'            => now(),
                    ];

                    // Create new order
                    Order::create($orderData);
                    $processedRows++;
                }
                
                usleep(100000); // Small delay to prevent overwhelming the server
            }
        }

        return response()->json([
            'message' => 'Closing Rina orders imported successfully (current month only)', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows,
            'not_current_month_rows' => $notCurrentMonthRows
        ]);
    }
    public function importClosingIis()
    {
        $this->googleSheetService->setSpreadsheetId('1hMubpvYFyDnPJB3NtiOwH-nH0Qwb9wz7Sq4laVESvPM');
        $range = 'Closing Iis!A:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $orderCountMap = [];
        
        // Get current month and year for filtering
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        // Initialize variables to track the last valid values for columns A-D
        $lastOrderDate = null;
        $lastCustomerName = null;
        $lastShippingAddress = null;
        $lastPhoneNumber = null;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if no product (Column E) or amount (Column H) is provided
                if (empty($row[4]) || empty($row[7])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                $isCurrentMonth = false;
                
                if (!empty($row[0])) {
                    try {
                        $dateStr = $row[0];
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                            $day = $matches[1];
                            $month = $matches[2];
                            $year = $matches[3];
                            $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($month == $currentMonth && $year == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        } else {
                            $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                            $parsedDate = Carbon::parse($dateStr);
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, use the last valid date if available
                        $orderDate = $lastOrderDate;
                        if (empty($orderDate)) {
                            $skippedRows++;
                            continue; // Skip if no valid date can be determined
                        }
                        
                        // Check if the last valid date is from current month
                        $parsedDate = Carbon::parse($orderDate);
                        $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                    }
                } else {
                    // If no date provided, use the last valid date if available
                    $orderDate = $lastOrderDate;
                    if (empty($orderDate)) {
                        $skippedRows++;
                        continue; // Skip if no valid date can be determined
                    }
                    
                    // Check if the last valid date is from current month
                    $parsedDate = Carbon::parse($orderDate);
                    $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                }
                
                // Skip if not from current month
                if (!$isCurrentMonth) {
                    $skippedRows++;
                    continue;
                }
                
                // Process customer name (Column B)
                if (!empty($row[1])) {
                    $lastCustomerName = $row[1]; // Update the last valid customer name
                }
                
                // Skip if we still don't have a customer name
                if (empty($lastCustomerName)) {
                    $skippedRows++;
                    continue;
                }
                
                // Process shipping address (Column C)
                if (!empty($row[2])) {
                    $lastShippingAddress = $row[2]; // Update the last valid shipping address
                }
                
                // Process phone number (Column D)
                if (!empty($row[3])) {
                    // Remove non-digit characters
                    $cleanedPhone = preg_replace('/\D/', '', $row[3]);
                    
                    // Ensure it starts with 0
                    if (!empty($cleanedPhone) && substr($cleanedPhone, 0, 1) !== '0') {
                        $cleanedPhone = '0' . $cleanedPhone;
                    }
                    
                    $lastPhoneNumber = $cleanedPhone; // Update the last valid phone number
                }
                
                // Process SKUs (Column F)
                $skus = [];
                if (!empty($row[5])) {
                    // Split SKUs by comma and trim whitespace
                    $skus = array_map('trim', explode(',', $row[5]));
                } else {
                    $skus = [null]; // Create at least one row even if SKU is missing
                }
                
                // Parse the amount/price from Column H
                $amount = $this->parseAmount($row[7]);
                
                // Double-check that amount isn't null after parsing
                if ($amount === null) {
                    $skippedRows++;
                    continue;
                }
                
                // Create a separate order entry for each SKU
                foreach ($skus as $index => $sku) {
                    // Generate order number
                    $month = Carbon::parse($orderDate)->format('m');
                    $year = Carbon::parse($orderDate)->format('y');
                    $employeeId = 'CLEOAZ111'; // As specified in requirements
                    $monthYearKey = $month . $year . $employeeId;
                    
                    if (!isset($orderCountMap[$monthYearKey])) {
                        // Check if there are existing orders in the database for this month/year/employee
                        $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                        
                        if ($lastOrder) {
                            // Extract the order number from the last order
                            $parts = explode('/', $lastOrder->id_order);
                            $lastOrderNumber = (int)end($parts);
                            $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                        } else {
                            $orderCountMap[$monthYearKey] = 1;
                        }
                    } else {
                        $orderCountMap[$monthYearKey]++;
                    }
                    
                    $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                    $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                    
                    // Only assign the amount to the first SKU, set 0 for others
                    $orderAmount = ($index === 0) ? $amount : 0;
                    $orderPrice = ($index === 0) ? $amount : 0;
                    
                    // Check for duplicates based on the combination of fields from the sheet
                    $existingOrder = Order::where('date', $orderDate)
                                    ->where('product', $row[4] ?? null)
                                    ->where('sku', $sku)
                                    ->where('qty', $row[6] ?? null)
                                    ->where('amount', $orderAmount)
                                    ->where('customer_name', $lastCustomerName)
                                    ->where('tenant_id', $tenant_id)
                                    ->first();

                    if ($existingOrder) {
                        // Skip this row as it already exists
                        $duplicateRows++;
                        continue;
                    }
                    
                    $orderData = [
                        'date'                  => $orderDate,
                        'process_at'            => null,
                        'id_order'              => $generatedIdOrder,
                        'sales_channel_id'      => 10, // As specified
                        'customer_name'         => $lastCustomerName,
                        'customer_phone_number' => $lastPhoneNumber,
                        'product'               => $row[4] ?? null,
                        'qty'                   => $row[6] ?? null,
                        'receipt_number'        => "-",
                        'shipment'              => "-",
                        'payment_method'        => $row[8] ?? null,
                        'sku'                   => $sku,
                        'variant'               => null,
                        'price'                 => $orderPrice,
                        'username'              => $lastCustomerName, // Same as customer_name
                        'shipping_address'      => $lastShippingAddress,
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $orderAmount,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => 'reported',
                        'updated_at'            => now(),
                        'created_at'            => now(),
                    ];

                    // Create new order
                    Order::create($orderData);
                    $processedRows++;
                }
                
                usleep(100000); // Small delay to prevent overwhelming the server
            }
        }

        return response()->json([
            'message' => 'Closing Iis orders imported successfully (current month only)', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }
    public function importClosingKiki()
    {
        $this->googleSheetService->setSpreadsheetId('1hMubpvYFyDnPJB3NtiOwH-nH0Qwb9wz7Sq4laVESvPM');
        $range = 'Closing Kiki!A:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $orderCountMap = [];
        
        // Get current month and year for filtering
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        // Initialize variables to track the last valid values for columns A-D
        $lastOrderDate = null;
        $lastCustomerName = null;
        $lastShippingAddress = null;
        $lastPhoneNumber = null;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if no product (Column E) or amount (Column H) is provided
                if (empty($row[4]) || empty($row[7])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                $isCurrentMonth = false;
                
                if (!empty($row[0])) {
                    try {
                        $dateStr = $row[0];
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                            $day = $matches[1];
                            $month = $matches[2];
                            $year = $matches[3];
                            $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($month == $currentMonth && $year == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        } else {
                            $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                            $parsedDate = Carbon::parse($dateStr);
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, use the last valid date if available
                        $orderDate = $lastOrderDate;
                        if (empty($orderDate)) {
                            $skippedRows++;
                            continue; // Skip if no valid date can be determined
                        }
                        
                        // Check if the last valid date is from current month
                        $parsedDate = Carbon::parse($orderDate);
                        $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                    }
                } else {
                    // If no date provided, use the last valid date if available
                    $orderDate = $lastOrderDate;
                    if (empty($orderDate)) {
                        $skippedRows++;
                        continue; // Skip if no valid date can be determined
                    }
                    
                    // Check if the last valid date is from current month
                    $parsedDate = Carbon::parse($orderDate);
                    $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                }
                
                // Skip if not from current month
                if (!$isCurrentMonth) {
                    $skippedRows++;
                    continue;
                }
                
                // Process customer name (Column B)
                if (!empty($row[1])) {
                    $lastCustomerName = $row[1]; // Update the last valid customer name
                }
                
                // Skip if we still don't have a customer name
                if (empty($lastCustomerName)) {
                    $skippedRows++;
                    continue;
                }
                
                // Process shipping address (Column C)
                if (!empty($row[2])) {
                    $lastShippingAddress = $row[2]; // Update the last valid shipping address
                }
                
                // Process phone number (Column D)
                if (!empty($row[3])) {
                    // Remove non-digit characters
                    $cleanedPhone = preg_replace('/\D/', '', $row[3]);
                    
                    // Ensure it starts with 0
                    if (!empty($cleanedPhone) && substr($cleanedPhone, 0, 1) !== '0') {
                        $cleanedPhone = '0' . $cleanedPhone;
                    }
                    
                    $lastPhoneNumber = $cleanedPhone; // Update the last valid phone number
                }
                
                // Process SKUs (Column F)
                $skus = [];
                if (!empty($row[5])) {
                    // Split SKUs by comma and trim whitespace
                    $skus = array_map('trim', explode(',', $row[5]));
                } else {
                    $skus = [null]; // Create at least one row even if SKU is missing
                }
                
                // Parse the amount/price from Column H
                $amount = $this->parseAmount($row[7]);
                
                // Double-check that amount isn't null after parsing
                if ($amount === null) {
                    $skippedRows++;
                    continue;
                }
                
                // Create a separate order entry for each SKU
                foreach ($skus as $index => $sku) {
                    // Generate order number
                    $month = Carbon::parse($orderDate)->format('m');
                    $year = Carbon::parse($orderDate)->format('y');
                    $employeeId = 'CLEOAZ112'; // As specified in requirements
                    $monthYearKey = $month . $year . $employeeId;
                    
                    if (!isset($orderCountMap[$monthYearKey])) {
                        // Check if there are existing orders in the database for this month/year/employee
                        $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                        
                        if ($lastOrder) {
                            // Extract the order number from the last order
                            $parts = explode('/', $lastOrder->id_order);
                            $lastOrderNumber = (int)end($parts);
                            $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                        } else {
                            $orderCountMap[$monthYearKey] = 1;
                        }
                    } else {
                        $orderCountMap[$monthYearKey]++;
                    }
                    
                    $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                    $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                    
                    // Only assign the amount to the first SKU, set 0 for others
                    $orderAmount = ($index === 0) ? $amount : 0;
                    $orderPrice = ($index === 0) ? $amount : 0;
                    
                    // Check for duplicates based on the combination of fields from the sheet
                    $existingOrder = Order::where('date', $orderDate)
                                    ->where('product', $row[4] ?? null)
                                    ->where('sku', $sku)
                                    ->where('qty', $row[6] ?? null)
                                    ->where('amount', $orderAmount)
                                    ->where('customer_name', $lastCustomerName)
                                    ->where('tenant_id', $tenant_id)
                                    ->first();

                    if ($existingOrder) {
                        // Skip this row as it already exists
                        $duplicateRows++;
                        continue;
                    }
                    
                    $orderData = [
                        'date'                  => $orderDate,
                        'process_at'            => null,
                        'id_order'              => $generatedIdOrder,
                        'sales_channel_id'      => 10, // As specified
                        'customer_name'         => $lastCustomerName,
                        'customer_phone_number' => $lastPhoneNumber,
                        'product'               => $row[4] ?? null,
                        'qty'                   => $row[6] ?? null,
                        'receipt_number'        => "-",
                        'shipment'              => "-",
                        'payment_method'        => $row[8] ?? null,
                        'sku'                   => $sku,
                        'variant'               => null,
                        'price'                 => $orderPrice,
                        'username'              => $lastCustomerName, // Same as customer_name
                        'shipping_address'      => $lastShippingAddress,
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $orderAmount,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => 'reported',
                        'updated_at'            => now(),
                        'created_at'            => now(),
                    ];

                    // Create new order
                    Order::create($orderData);
                    $processedRows++;
                }
                
                usleep(100000); // Small delay to prevent overwhelming the server
            }
        }

        return response()->json([
            'message' => 'Closing Kiki orders imported successfully (current month only)', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }
    public function importClosingZalsa()
    {
        $this->googleSheetService->setSpreadsheetId('1hMubpvYFyDnPJB3NtiOwH-nH0Qwb9wz7Sq4laVESvPM');
        $range = 'Closing Zalsa!A:I';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $duplicateRows = 0;
        $orderCountMap = [];
        
        // Get current month and year for filtering
        $currentMonth = Carbon::now()->format('m');
        $currentYear = Carbon::now()->format('Y');
        
        // Initialize variables to track the last valid values for columns A-D
        $lastOrderDate = null;
        $lastCustomerName = null;
        $lastShippingAddress = null;
        $lastPhoneNumber = null;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if no product (Column E) or amount (Column H) is provided
                if (empty($row[4]) || empty($row[7])) {
                    $skippedRows++;
                    continue;
                }
                
                // Process date (Column A)
                $orderDate = null;
                $isCurrentMonth = false;
                
                if (!empty($row[0])) {
                    try {
                        $dateStr = $row[0];
                        if (preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $dateStr, $matches)) {
                            $day = $matches[1];
                            $month = $matches[2];
                            $year = $matches[3];
                            $orderDate = Carbon::createFromDate($year, $month, $day)->format('Y-m-d');
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($month == $currentMonth && $year == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        } else {
                            $orderDate = Carbon::parse($dateStr)->format('Y-m-d');
                            $parsedDate = Carbon::parse($dateStr);
                            
                            // Check if date is from current month
                            $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                            
                            $lastOrderDate = $orderDate; // Update the last valid date
                        }
                    } catch (\Exception $e) {
                        // If date parsing fails, use the last valid date if available
                        $orderDate = $lastOrderDate;
                        if (empty($orderDate)) {
                            $skippedRows++;
                            continue; // Skip if no valid date can be determined
                        }
                        
                        // Check if the last valid date is from current month
                        $parsedDate = Carbon::parse($orderDate);
                        $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                    }
                } else {
                    // If no date provided, use the last valid date if available
                    $orderDate = $lastOrderDate;
                    if (empty($orderDate)) {
                        $skippedRows++;
                        continue; // Skip if no valid date can be determined
                    }
                    
                    // Check if the last valid date is from current month
                    $parsedDate = Carbon::parse($orderDate);
                    $isCurrentMonth = ($parsedDate->format('m') == $currentMonth && $parsedDate->format('Y') == $currentYear);
                }
                
                // Skip if not from current month
                if (!$isCurrentMonth) {
                    $skippedRows++;
                    continue;
                }
                
                // Process customer name (Column B)
                if (!empty($row[1])) {
                    $lastCustomerName = $row[1]; // Update the last valid customer name
                }
                
                // Skip if we still don't have a customer name
                if (empty($lastCustomerName)) {
                    $skippedRows++;
                    continue;
                }
                
                // Process shipping address (Column C)
                if (!empty($row[2])) {
                    $lastShippingAddress = $row[2]; // Update the last valid shipping address
                }
                
                // Process phone number (Column D)
                if (!empty($row[3])) {
                    // Remove non-digit characters
                    $cleanedPhone = preg_replace('/\D/', '', $row[3]);
                    
                    // Ensure it starts with 0
                    if (!empty($cleanedPhone) && substr($cleanedPhone, 0, 1) !== '0') {
                        $cleanedPhone = '0' . $cleanedPhone;
                    }
                    
                    $lastPhoneNumber = $cleanedPhone; // Update the last valid phone number
                }
                
                // Process SKUs (Column F)
                $skus = [];
                if (!empty($row[5])) {
                    // Split SKUs by comma and trim whitespace
                    $skus = array_map('trim', explode(',', $row[5]));
                } else {
                    $skus = [null]; // Create at least one row even if SKU is missing
                }
                
                // Parse the amount/price from Column H
                $amount = $this->parseAmount($row[7]);
                
                // Double-check that amount isn't null after parsing
                if ($amount === null) {
                    $skippedRows++;
                    continue;
                }
                
                // Create a separate order entry for each SKU
                foreach ($skus as $index => $sku) {
                    // Generate order number
                    $month = Carbon::parse($orderDate)->format('m');
                    $year = Carbon::parse($orderDate)->format('y');
                    $employeeId = 'CLEOAZ113'; // As specified in requirements
                    $monthYearKey = $month . $year . $employeeId;
                    
                    if (!isset($orderCountMap[$monthYearKey])) {
                        // Check if there are existing orders in the database for this month/year/employee
                        $lastOrder = Order::where('id_order', 'like', "CLE/{$month}{$year}/{$employeeId}/%")
                                        ->orderBy('id_order', 'desc')
                                        ->first();
                        
                        if ($lastOrder) {
                            // Extract the order number from the last order
                            $parts = explode('/', $lastOrder->id_order);
                            $lastOrderNumber = (int)end($parts);
                            $orderCountMap[$monthYearKey] = $lastOrderNumber + 1;
                        } else {
                            $orderCountMap[$monthYearKey] = 1;
                        }
                    } else {
                        $orderCountMap[$monthYearKey]++;
                    }
                    
                    $orderNumber = str_pad($orderCountMap[$monthYearKey], 5, '0', STR_PAD_LEFT);
                    $generatedIdOrder = "CLE/{$month}{$year}/{$employeeId}/{$orderNumber}";
                    
                    // Only assign the amount to the first SKU, set 0 for others
                    $orderAmount = ($index === 0) ? $amount : 0;
                    $orderPrice = ($index === 0) ? $amount : 0;
                    
                    // Check for duplicates based on the combination of fields from the sheet
                    $existingOrder = Order::where('date', $orderDate)
                                    ->where('product', $row[4] ?? null)
                                    ->where('sku', $sku)
                                    ->where('qty', $row[6] ?? null)
                                    ->where('amount', $orderAmount)
                                    ->where('customer_name', $lastCustomerName)
                                    ->where('tenant_id', $tenant_id)
                                    ->first();

                    if ($existingOrder) {
                        // Skip this row as it already exists
                        $duplicateRows++;
                        continue;
                    }
                    
                    $orderData = [
                        'date'                  => $orderDate,
                        'process_at'            => null,
                        'id_order'              => $generatedIdOrder,
                        'sales_channel_id'      => 10, // As specified
                        'customer_name'         => $lastCustomerName,
                        'customer_phone_number' => $lastPhoneNumber,
                        'product'               => $row[4] ?? null,
                        'qty'                   => $row[6] ?? null,
                        'receipt_number'        => "-",
                        'shipment'              => "-",
                        'payment_method'        => $row[8] ?? null,
                        'sku'                   => $sku,
                        'variant'               => null,
                        'price'                 => $orderPrice,
                        'username'              => $lastCustomerName, // Same as customer_name
                        'shipping_address'      => $lastShippingAddress,
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $orderAmount,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => 'reported',
                        'updated_at'            => now(),
                        'created_at'            => now(),
                    ];

                    // Create new order
                    Order::create($orderData);
                    $processedRows++;
                }
                
                usleep(100000); // Small delay to prevent overwhelming the server
            }
        }

        return response()->json([
            'message' => 'Closing Zalsa orders imported successfully (current month only)', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'skipped_rows' => $skippedRows,
            'duplicate_rows' => $duplicateRows
        ]);
    }

    public function updateSuccessDateShopee()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Shopee Balance!A2:G';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;

        if (isset($sheetData[0]) && isset($sheetData[0][0]) && $sheetData[0][0] == 'Success Date') {
            array_shift($sheetData);
            $totalRows--;
        }

        // Track processed order IDs to prevent duplicates
        $processedOrderIds = [];

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[0]) || empty($row[3]) || $row[3] === "-") {
                    $skippedRows++;
                    continue;
                }
                
                $successDate = Carbon::parse($row[0])->format('Y-m-d'); // Column A = orders.success_date
                $inAmount = isset($row[1]) ? intval($row[1]) : 0; // Column B = orders.in_amount
                $feeAdmin = isset($row[2]) ? intval($row[2]) : 0; // Column C = orders.fee_admin
                $idOrder = $row[3]; // Column D = orders.id_order
                $status = "Selesai"; // Fixed value for orders.status
                
                // Skip if we've already processed this order ID
                if (in_array($idOrder, $processedOrderIds)) {
                    $skippedRows++;
                    continue;
                }
                
                // Add to processed IDs
                $processedOrderIds[] = $idOrder;
                
                // Check if order exists
                $order = Order::where('id_order', $idOrder)->first();
                
                if ($order) {
                    $order->success_date = $successDate;
                    $order->status = $status;
                    $order->in_amount = $inAmount;
                    $order->fee_admin = $feeAdmin;
                    $order->updated_at = now();
                    $order->save();
                    $updatedRows++;
                } else {
                    $orderData = [
                        'date'                  => Carbon::createFromFormat('Y-m-d', '1970-01-01')->format('Y-m-d'),
                        'process_at'            => null,
                        'id_order'              => $idOrder,
                        'sales_channel_id'      => 1,
                        'customer_name'         => "unknown",
                        'customer_phone_number' => "unknown",
                        'product'               => "unknown",
                        'qty'                   => 1,
                        'receipt_number'        => "unknown",
                        'shipment'              => "unknown",
                        'payment_method'        => "unknown",
                        'sku'                   => "unknown",
                        'variant'               => null,
                        'price'                 => $inAmount,
                        'username'              => "unknown",
                        'shipping_address'      => "unknown",
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $inAmount,
                        'in_amount'             => $inAmount,
                        'fee_admin'             => $feeAdmin,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => $status,
                        'updated_at'            => now(),
                        'created_at'            => now(),
                        'success_date'          => $successDate,
                    ];
                    
                    Order::create($orderData);
                    $newOrdersCreated++;
                }
                
                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Success dates updated successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'new_orders_created' => $newOrdersCreated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function updateSuccessDateTiktok()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Tiktok Processed!A:G';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;
        $errorRows = 0;

        // Always assume the first row is a header and skip it
        if (count($sheetData) > 0) {
            array_shift($sheetData);
            $totalRows--;
        }

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if order ID (Column A) is empty or success date (Column B) is empty or "-"
                if (empty($row[0]) || empty($row[1]) || $row[1] === "-") {
                    $skippedRows++;
                    continue;
                }
                
                $idOrder = $row[0]; // Column A = orders.id_order
                $successDate = Carbon::parse($row[1])->format('Y-m-d'); // Column B = orders.success_date
                $amount = isset($row[2]) ? intval($row[2]) : 0; // Column C = orders.in_amount
                $feeAdmin = isset($row[3]) ? intval($row[3]) : 0; // Column D = orders.fee_admin
                
                // Check if order exists
                $order = Order::where('id_order', $idOrder)->first();
                
                if ($order) {
                    $order->success_date = $successDate;
                    $order->status = "Selesai";
                    $order->in_amount = $amount;
                    $order->fee_admin = $feeAdmin;
                    $order->updated_at = now();
                    $order->save();
                    $updatedRows++;
                } else {
                    $orderData = [
                        'date'                  => $successDate,
                        'process_at'            => null,
                        'id_order'              => $idOrder,
                        'sales_channel_id'      => 4,
                        'customer_name'         => "unknown",
                        'customer_phone_number' => "unknown",
                        'product'               => "unknown",
                        'qty'                   => 1,
                        'receipt_number'        => "unknown",
                        'shipment'              => "unknown",
                        'payment_method'        => "unknown", 
                        'sku'                   => "unknown",
                        'variant'               => null,
                        'price'                 => $amount,
                        'username'              => "unknown",
                        'shipping_address'      => "unknown",
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $amount,
                        'in_amount'             => $amount,
                        'fee_admin'             => $feeAdmin,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => "Selesai",
                        'updated_at'            => now(),
                        'created_at'            => now(),
                        'success_date'          => $successDate,
                    ];
                    
                    Order::create($orderData);
                    $newOrdersCreated++;
                }
                
                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Success dates updated successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'new_orders_created' => $newOrdersCreated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function updateSuccessDateLazada()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Lazada Balance Processed!A:G';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;
        $ordersUpdated = 0;

        if (count($sheetData) > 0) {
            array_shift($sheetData);
            $totalRows--;
        }
        DB::beginTransaction();
        
        try {
            foreach ($sheetData as $row) {
                // Skip if order ID (Column A) is empty or success date (Column B) is empty or order date (Column D) is empty or SKU (Column E) is empty
                if (empty($row[0]) || empty($row[1]) || empty($row[3]) || empty($row[4])) {
                    $skippedRows++;
                    continue;
                }
                
                try {
                    $idOrder = $row[0]; // Column A = orders.id_order
                    $successDate = Carbon::parse($row[1])->format('Y-m-d'); // Column B = orders.success_date
                    $inAmount = isset($row[2]) ? intval($row[2]) : 0; // Column C = orders.in_amount
                    
                    // Parse the date in format "06 Mar 2025"
                    $orderDate = Carbon::createFromFormat('d M Y', $row[3])->format('Y-m-d'); // Column D = orders.date
                    
                    $sku = $row[4]; // Column E = orders.sku
                    $feeAdmin = isset($row[5]) ? intval($row[5]) : 0; // Column F = orders.fee_admin
                    
                    // Try to find existing order with the same id_order and sku
                    $existingOrder = Order::where('id_order', $idOrder)
                        ->where('sku', $sku)
                        ->where('tenant_id', $tenant_id)
                        ->first();
                    
                    if ($existingOrder) {
                        // Update existing order
                        $existingOrder->success_date = $successDate;
                        $existingOrder->in_amount = $inAmount;
                        $existingOrder->fee_admin = $feeAdmin;
                        $existingOrder->updated_at = now();
                        $existingOrder->save();
                        
                        $ordersUpdated++;
                    } else {
                        // Create new order
                        $orderData = [
                            'date'                  => $orderDate,
                            'process_at'            => null,
                            'id_order'              => $idOrder,
                            'sales_channel_id'      => 2,
                            'customer_name'         => "unknown",
                            'customer_phone_number' => "unknown",
                            'product'               => "unknown",
                            'qty'                   => 1,
                            'receipt_number'        => "unknown",
                            'shipment'              => "unknown",
                            'payment_method'        => "unknown",
                            'sku'                   => $sku,
                            'variant'               => null,
                            'price'                 => $inAmount,
                            'username'              => "unknown",
                            'shipping_address'      => "unknown",
                            'city'                  => null,
                            'province'              => null,
                            'amount'                => $inAmount,
                            'in_amount'             => $inAmount,
                            'fee_admin'             => $feeAdmin,
                            'tenant_id'             => $tenant_id,
                            'is_booking'            => 0,
                            'status'                => "Selesai",
                            'success_date'          => $successDate,
                            'created_at'            => now(),
                            'updated_at'            => now(),
                        ];
                        
                        Order::create($orderData);
                        $newOrdersCreated++;
                    }
                    
                    $processedRows++;
                    
                } catch (\Exception $e) {
                    \Log::warning("Error processing row: " . json_encode($row) . " - " . $e->getMessage());
                    $skippedRows++;
                }
            }
            
            // Commit all changes
            DB::commit();
            
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error processing Lazada data: " . $e->getMessage());
            
            return response()->json([
                'message' => 'Error processing Lazada data', 
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Lazada success dates processed successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'new_orders_created' => $newOrdersCreated,
            'orders_updated' => $ordersUpdated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function updateSuccessDateTokopedia()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Tokopedia Balance!A:D';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;

        // Always assume the first row is a header and skip it
        if (count($sheetData) > 0) {
            array_shift($sheetData);
            $totalRows--;
        }

        // Track processed order IDs to prevent duplicates
        $processedOrderIds = [];

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $rowIndex => $row) {
                // Skip if success date (Column A) is empty or id_order (Column B) is empty
                if (empty($row[0]) || empty($row[1])) {
                    $skippedRows++;
                    continue;
                }
                
                // Parse the date part only from Column A
                $successDateWithTime = Carbon::parse($row[0]);
                $successDate = $successDateWithTime->format('Y-m-d');
                
                $idOrder = $row[1]; // Column B = orders.id_order
                
                // Get in_amount from Column C
                $inAmount = 0;
                if (isset($row[2]) && !empty($row[2])) {
                    // Remove dots (used as thousand separators) and convert to integer
                    $cleanInAmount = str_replace('.', '', $row[2]);
                    $inAmount = intval($cleanInAmount);
                }
                
                // Get fee_admin from Column D
                $feeAdmin = 0;
                if (isset($row[3]) && !empty($row[3])) {
                    // Remove dots (used as thousand separators) and convert to integer
                    $cleanFeeAdmin = str_replace('.', '', $row[3]);
                    $feeAdmin = intval($cleanFeeAdmin);
                }
                
                // Skip if we've already processed this order ID
                if (in_array($idOrder, $processedOrderIds)) {
                    $skippedRows++;
                    continue;
                }
                
                // Add to processed IDs
                $processedOrderIds[] = $idOrder;
                
                // Check if order exists
                $order = Order::where('id_order', $idOrder)->first();
                
                if ($order) {
                    $order->success_date = $successDate;
                    $order->status = "Selesai";
                    $order->in_amount = $inAmount;
                    $order->fee_admin = $feeAdmin;
                    $order->updated_at = now();
                    $order->save();
                    $updatedRows++;
                } else {
                    $orderData = [
                        'date'                  => $successDate,
                        'process_at'            => null,
                        'id_order'              => $idOrder,
                        'sales_channel_id'      => 3, // Tokopedia = 3
                        'customer_name'         => "unknown",
                        'customer_phone_number' => "unknown",
                        'product'               => "unknown",
                        'qty'                   => 1,
                        'receipt_number'        => "unknown",
                        'shipment'              => "unknown",
                        'payment_method'        => "unknown", 
                        'sku'                   => "unknown",
                        'variant'               => null,
                        'price'                 => $inAmount,
                        'username'              => "unknown",
                        'shipping_address'      => "unknown",
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $inAmount,
                        'in_amount'             => $inAmount,
                        'fee_admin'             => $feeAdmin,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => "Selesai",
                        'updated_at'            => now(),
                        'created_at'            => now(),
                        'success_date'          => $successDate,
                    ];
                    
                    Order::create($orderData);
                    $newOrdersCreated++;
                }
                
                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Tokopedia success dates updated successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'new_orders_created' => $newOrdersCreated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function updateSuccessDateShopee2()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Shopee 2 Balance!A2:G';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;

        if (isset($sheetData[0]) && isset($sheetData[0][0]) && $sheetData[0][0] == 'Success Date') {
            array_shift($sheetData);
            $totalRows--;
        }

        // Track processed order IDs to prevent duplicates
        $processedOrderIds = [];

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[0]) || empty($row[3]) || $row[3] === "-") {
                    $skippedRows++;
                    continue;
                }
                
                $successDate = Carbon::parse($row[0])->format('Y-m-d'); // Column A = orders.success_date
                $inAmount = isset($row[1]) ? intval($row[1]) : 0; // Column B = orders.in_amount
                $feeAdmin = isset($row[2]) ? intval($row[2]) : 0; // Column C = orders.fee_admin
                $idOrder = $row[3]; // Column D = orders.id_order
                $status = "Selesai"; // Fixed value for orders.status
                
                // Skip if we've already processed this order ID
                if (in_array($idOrder, $processedOrderIds)) {
                    $skippedRows++;
                    continue;
                }
                
                // Add to processed IDs
                $processedOrderIds[] = $idOrder;
                
                // Check if order exists
                $order = Order::where('id_order', $idOrder)->first();
                
                if ($order) {
                    $order->success_date = $successDate;
                    $order->status = $status;
                    $order->in_amount = $inAmount;
                    $order->fee_admin = $feeAdmin;
                    $order->updated_at = now();
                    $order->sales_channel_id = 8;
                    $order->save();
                    $updatedRows++;
                } else {
                    $orderData = [
                        'date'                  => Carbon::createFromFormat('Y-m-d', '1970-01-01')->format('Y-m-d'),
                        'process_at'            => null,
                        'id_order'              => $idOrder,
                        'sales_channel_id'      => 8,
                        'customer_name'         => "unknown",
                        'customer_phone_number' => "unknown",
                        'product'               => "unknown",
                        'qty'                   => 1,
                        'receipt_number'        => "unknown",
                        'shipment'              => "unknown",
                        'payment_method'        => "unknown",
                        'sku'                   => "unknown",
                        'variant'               => null,
                        'price'                 => $inAmount,
                        'username'              => "unknown",
                        'shipping_address'      => "unknown",
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $inAmount,
                        'in_amount'             => $inAmount,
                        'fee_admin'             => $feeAdmin,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => $status,
                        'updated_at'            => now(),
                        'created_at'            => now(),
                        'success_date'          => $successDate,
                    ];
                    
                    Order::create($orderData);
                    $newOrdersCreated++;
                }
                
                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Success dates updated successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'new_orders_created' => $newOrdersCreated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function updateSuccessDateShopee3()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Shopee 3 Balance!A2:G';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $skippedRows = 0;
        $newOrdersCreated = 0;

        if (isset($sheetData[0]) && isset($sheetData[0][0]) && $sheetData[0][0] == 'Success Date') {
            array_shift($sheetData);
            $totalRows--;
        }

        // Track processed order IDs to prevent duplicates
        $processedOrderIds = [];

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                if (empty($row[0]) || empty($row[3]) || $row[3] === "-") {
                    $skippedRows++;
                    continue;
                }
                
                $successDate = Carbon::parse($row[0])->format('Y-m-d'); // Column A = orders.success_date
                $inAmount = isset($row[1]) ? intval($row[1]) : 0; // Column B = orders.in_amount
                $feeAdmin = isset($row[2]) ? intval($row[2]) : 0; // Column C = orders.fee_admin
                $idOrder = $row[3]; // Column D = orders.id_order
                $status = "Selesai"; // Fixed value for orders.status
                
                // Skip if we've already processed this order ID
                if (in_array($idOrder, $processedOrderIds)) {
                    $skippedRows++;
                    continue;
                }
                
                // Add to processed IDs
                $processedOrderIds[] = $idOrder;
                
                // Check if order exists
                $order = Order::where('id_order', $idOrder)->first();
                
                if ($order) {
                    $order->success_date = $successDate;
                    $order->status = $status;
                    $order->in_amount = $inAmount;
                    $order->fee_admin = $feeAdmin;
                    $order->updated_at = now();
                    $order->save();
                    $updatedRows++;
                } else {
                    $orderData = [
                        'date'                  => Carbon::createFromFormat('Y-m-d', '1970-01-01')->format('Y-m-d'),
                        'process_at'            => null,
                        'id_order'              => $idOrder,
                        'sales_channel_id'      => 9,
                        'customer_name'         => "unknown",
                        'customer_phone_number' => "unknown",
                        'product'               => "unknown",
                        'qty'                   => 1,
                        'receipt_number'        => "unknown",
                        'shipment'              => "unknown",
                        'payment_method'        => "unknown",
                        'sku'                   => "unknown",
                        'variant'               => null,
                        'price'                 => $inAmount,
                        'username'              => "unknown",
                        'shipping_address'      => "unknown",
                        'city'                  => null,
                        'province'              => null,
                        'amount'                => $inAmount,
                        'in_amount'             => $inAmount,
                        'fee_admin'             => $feeAdmin,
                        'tenant_id'             => $tenant_id,
                        'is_booking'            => 0,
                        'status'                => $status,
                        'updated_at'            => now(),
                        'created_at'            => now(),
                        'success_date'          => $successDate,
                    ];
                    
                    Order::create($orderData);
                    $newOrdersCreated++;
                }
                
                $processedRows++;
            }
            usleep(100000);
        }

        return response()->json([
            'message' => 'Success dates updated successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'updated_rows' => $updatedRows,
            'new_orders_created' => $newOrdersCreated,
            'skipped_rows' => $skippedRows
        ]);
    }
    public function importMissingData()
    {
        $this->googleSheetService->setSpreadsheetId('1RDC3Afs4wzaO3S36rvX35xB_D_zuqVs5vfMe7TI8vRY');
        $range = 'Import Missing Data!A2:T';
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $updatedRows = 0;
        $newOrdersCreated = 0;
        $skippedRows = 0;
        $deletedRows = 0;

        // First, collect all the data to process
        $ordersToProcess = [];
        foreach ($sheetData as $row) {
            // Skip if id_order is empty
            if (empty($row[0])) {
                $skippedRows++;
                continue;
            }
            
            $idOrder = $row[0]; // Column A = orders.id_order
            $sku = isset($row[6]) ? $row[6] : "unknown"; // Column G = orders.sku
            
            // Add to processing queue with a composite key of id_order + sku
            $compositeKey = $idOrder . '|' . $sku;
            $ordersToProcess[$compositeKey] = $row;
        }

        // Process in chunks
        foreach (array_chunk($ordersToProcess, $chunkSize, true) as $chunk) {
            foreach ($chunk as $compositeKey => $row) {
                list($idOrder, $sku) = explode('|', $compositeKey);
                
                // Parse date from Column D
                $date = Carbon::parse($row[3])->format('Y-m-d'); // Column D = orders.date
                
                $amount = isset($row[8]) ? intval($row[8]) : 0; // Column I = orders.amount
                $variant = isset($row[7]) ? $row[7] : null; // Column H = orders.variant
                $price = isset($row[8]) ? intval($row[8]) : 0; // Column I = orders.price
                $qty = isset($row[9]) ? intval($row[9]) : 1; // Column J = orders.qty
                $username = isset($row[10]) ? $row[10] : "unknown"; // Column K = orders.username
                $customerName = isset($row[11]) ? $row[11] : "unknown"; // Column L = orders.customer_name
                $customerPhoneNumber = isset($row[12]) ? $row[12] : "unknown"; // Column M = orders.customer_phone_number
                $shippingAddress = isset($row[13]) ? $row[13] : "unknown"; // Column N = orders.shipping_address
                $city = isset($row[14]) ? $row[14] : null; // Column O = orders.city
                $province = isset($row[15]) ? $row[15] : null; // Column P = orders.province
                
                // Determine sales_channel_id based on Column Q
                $salesChannelId = 1; // Default
                if (isset($row[16]) && $row[16] == "Shopee") {
                    $salesChannelId = 1;
                }
                
                $inAmount = isset($row[17]) ? intval($row[17]) : 0; // Column R = orders.in_amount
                
                // Parse success_date from Column S
                $successDate = null;
                if (isset($row[18]) && !empty($row[18])) {
                    $successDate = Carbon::parse($row[18])->format('Y-m-d'); // Column S = orders.success_date
                }
                
                $feeAdmin = isset($row[19]) ? intval($row[19]) : 0; // Column T = orders.fee_admin
                
                // Delete existing order with same id_order AND sku if it exists
                $deleted = Order::where('id_order', $idOrder)
                    ->where('sku', $sku)
                    ->delete();
                    
                if ($deleted) {
                    $deletedRows += $deleted;
                }
                
                // Create new order data
                $orderData = [
                    'id_order'              => $idOrder,
                    'date'                  => $date,
                    'process_at'            => null,
                    'product'               => "unknown",
                    'receipt_number'        => "-",
                    'shipment'              => "unknown",
                    'payment_method'        => "unknown",
                    'amount'                => $amount,
                    'tenant_id'             => $tenant_id,
                    'is_booking'            => 0,
                    'status'                => "Selesai",
                    'sku'                   => $sku,
                    'variant'               => $variant,
                    'price'                 => $price,
                    'qty'                   => $qty,
                    'username'              => $username,
                    'customer_name'         => $customerName,
                    'customer_phone_number' => $customerPhoneNumber,
                    'shipping_address'      => $shippingAddress,
                    'city'                  => $city,
                    'province'              => $province,
                    'sales_channel_id'      => $salesChannelId,
                    'in_amount'             => $inAmount,
                    'success_date'          => $successDate,
                    'fee_admin'             => $feeAdmin,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ];
                
                // Create new order
                Order::create($orderData);
                $newOrdersCreated++;
                $processedRows++;
            }
            usleep(100000); // Slight delay between processing chunks
        }

        return response()->json([
            'message' => 'Missing data imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows,
            'new_orders_created' => $newOrdersCreated,
            'deleted_rows' => $deletedRows,
            'skipped_rows' => $skippedRows
        ]);
    }
}
