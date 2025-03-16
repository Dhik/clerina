<?php

namespace App\Domain\Order\Controllers;

use App\Domain\Order\BLL\Order\OrderBLLInterface;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\Exports\OrdersExport;
use App\Domain\Order\Exports\SkuQuantitiesExport;
use App\Domain\Order\Exports\UniqueSkuExport;
use App\Domain\Order\Exports\OrderTemplateExport;
use App\Domain\Order\Models\Order;
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
        $startDate = Carbon::now()->subDays(3)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');

        $totals = Order::select(DB::raw('date, SUM(amount) AS total_amount'))
                        ->where('tenant_id', Auth::user()->current_tenant_id)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->groupBy('date')
                        ->get();
        foreach ($totals as $total) {
            $formattedDate = Carbon::parse($total->date)->format('Y-m-d');
            Sales::where('tenant_id', Auth::user()->current_tenant_id)
                ->where('date', $formattedDate)
                ->update(['turnover' => $total->total_amount]);
        }
        return response()->json(['message' => 'Sales turnover updated successfully']);
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
    public function importOrdersTokopedia()
    {
        set_time_limit(0);
        $range = 'Tokopedia Processed!A2:P'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                $orderData = [
                    'date' => !empty($row[1]) ? Carbon::createFromFormat('d-m-Y H:i:s', $row[1])->format('Y-m-d') : null,
                    'process_at'           => null,
                    'id_order'             => $row[0] ?? null,
                    'sales_channel_id'     => 3, // Tokopedia
                    'customer_name'        => $row[7] ?? null,
                    'customer_phone_number' => $row[8] ?? null,
                    'product'              => $row[2] ?? null,
                    'qty'                  => $row[5] ?? null,
                    'receipt_number'       => $row[14] ?? null,
                    'shipment'             => $row[15] ?? null,
                    'payment_method'       => null,
                    'sku'                  => $row[4] ?? null,
                    'variant'              => $row[3] ?? null,
                    'price'                => $row[6] ?? null,
                    'username'             => $row[7] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount'               => $row[6] ?? null,
                    'tenant_id'            => $tenant_id,
                    'is_booking'           => 0,
                    'status'               => $row[13] ?? null,
                    'created_at'           => now(),
                    'updated_at'           => now(),
                ];

                // Check if order with the same id_order, product, sku exists
                $order = Order::where('id_order', $orderData['id_order'])
                            ->where('product', $orderData['product'])
                            ->where('sku', $orderData['sku'])
                            ->first();

                // If order doesn't exist, create it
                if (!$order) {
                    Order::create($orderData);
                    $processedRows++;
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tokopedia orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
        ]);
    }
    public function importOrdersShopee()
    {
        set_time_limit(0);
        $range = 'Shopee Processed!A2:R'; 
        $sheetData = $this->googleSheetService->getSheetData($range);

        $tenant_id = 1;
        $chunkSize = 50;
        $totalRows = count($sheetData);
        $processedRows = 0;
        $skippedRows = 0;

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                // Skip the entire row if date column is empty
                if (empty($row[3])) {
                    $skippedRows++;
                    continue;
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
                    'price'                => $row[14] ?? null,
                    'username'             => $row[6] ?? null,
                    'shipping_address'     => $row[9] ?? null,
                    'city'                 => $row[10] ?? null,
                    'province'             => $row[11] ?? null,
                    'amount'               => $row[16] ?? null, // Column Q
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
                            ->first();

                // If order doesn't exist, create it
                if (!$order) {
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

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                try {
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
                        'created_at'           => now(),
                        'updated_at'           => now(),
                    ];

                    // Check if order with the same id_order, product, sku exists
                    $order = Order::where('id_order', $orderData['id_order'])
                                ->where('product', $orderData['product'])
                                ->where('sku', $orderData['sku'])
                                ->first();

                    // If order doesn't exist, create it
                    if (!$order) {
                        Order::create($orderData);
                        $processedRows++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Error processing Tiktok order row: " . json_encode($row) . " Error: " . $e->getMessage());
                    continue; // Skip this row and continue with the next
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Tiktok orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
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

        foreach (array_chunk($sheetData, $chunkSize) as $chunk) {
            foreach ($chunk as $row) {
                try {
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
                        'qty'                  => $row[10] ?? null,
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

                    // Check for identical record
                    $query = Order::query();
                    foreach ($orderData as $field => $value) {
                        if ($value !== null) {
                            $query->where($field, $value);
                        }
                    }
                    
                    $existingOrder = $query->first();

                    // If no identical order exists, create it
                    if (!$existingOrder) {
                        // Add timestamps for creation
                        $orderData['created_at'] = now();
                        $orderData['updated_at'] = now();
                        
                        Order::create($orderData);
                        $processedRows++;
                    }
                } catch (\Exception $e) {
                    \Log::error("Error processing Lazada order row: " . json_encode($row) . " Error: " . $e->getMessage());
                    continue; // Skip this row and continue with the next
                }
            }
            usleep(100000); // Small delay to prevent overwhelming the server
        }

        return response()->json([
            'message' => 'Lazada orders imported successfully', 
            'total_rows' => $totalRows,
            'processed_rows' => $processedRows
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
}
