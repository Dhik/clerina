<?php

namespace App\Domain\Order\Controllers;

use App\Domain\Order\BLL\Order\OrderBLLInterface;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\Exports\OrdersExport;
use App\Domain\Order\Exports\UniqueSkuExport;
use App\Domain\Order\Exports\OrderTemplateExport;
use App\Domain\Order\Models\Order;
use App\Domain\Sales\Models\Sales;
use App\Domain\Order\Requests\OrderStoreRequest;
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

class OrderController extends Controller
{
    public function __construct(
        protected OrderBLLInterface $orderBLL,
        protected OrderDALInterface $orderDAL,
        protected SalesChannelBLLInterface $salesChannelBLL
    ) {

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

        return view('admin.order.index', compact('salesChannels', 'cities'));
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
                            'username' => $orderData['customer_username'],
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
                        ->where('tenant_id', 1)
                        ->whereBetween('date', [$startDate, $endDate])
                        ->groupBy('date')
                        ->get();
        foreach ($totals as $total) {
            $formattedDate = Carbon::parse($total->date)->format('Y-m-d');
            Sales::where('tenant_id', 1)
                ->where('date', $formattedDate)
                ->update(['turnover' => $total->total_amount]);
        }
        return response()->json(['message' => 'Sales turnover updated successfully']);
    }

    public function fetchAllOrders(): JsonResponse
    {
        set_time_limit(0);

        $client = new Client();
        $baseUrl = 'https://wms-api.clerinagroup.com/v1/open/orders/page';
        $headers = [
            'x-api-key' => 'f5c80067e1da48e0b2b124558f5c533f1fda9fea72aa4a2a866c6a15a1a31ca8'
        ];
        $statuses = ['paid', 'process', 'pick', 'packing', 'packed', 'sent', 'completed'];
        $startDate = Carbon::now()->subDays(1)->format('Y-m-d');
        $endDate = Carbon::now()->format('Y-m-d');
        $allOrders = [];

        foreach ($statuses as $status) {
            $page = 1;
            $totalPages = 1;

            do {
                $response = $client->get($baseUrl, [
                    'headers' => $headers,
                    'query' => [
                        'status' => $status,
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'page' => $page
                    ]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return response()->json(['error' => 'Failed to fetch data from API', 'status_code' => $response->getStatusCode()], 500);
                }

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

                    // Check if the order already exists
                    $existingOrder = Order::where('id_order', $orderData['reference_no'])->first();

                    if ($existingOrder) {
                        // Update only amount and sku if the order exists
                        $amount = $orderData['amount'] - $orderData['shipping_fee'];
                        $amount = $amount < 0 ? 0 : $amount;

                        $existingOrder->update([
                            'amount' => $amount,
                            'sku' => $orderData['product_summary'],
                            'sales_channel_id' => $this->getSalesChannelId($orderData['channel_name']),
                            'tenant_id' => $this->determineTenantId($orderData['channel_name'], $orderData['product_summary'], $orderData['integration_store']),
                        ]);

                    } else {
                        // Create new order if it doesn't exist
                        Order::create([
                            'id_order' => $orderData['reference_no'],
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
                            'username' => $orderData['customer_username'],
                            'tenant_id' => $this->determineTenantId($orderData['channel_name'], $orderData['product_summary'], $orderData['integration_store']),
                        ]);
                    }
                }

                $page++;
            } while ($page <= $totalPages);
        }

        return response()->json(['message' => 'Orders fetched and saved successfully']);
    }


    private function getSalesChannelId($channelName)
    {
        return match ($channelName) {
            'Tiktok' => 4,
            'Shopee' => 1,
            'Lazada' => 2,
            'Manual'=> 5,
            'Tokopedia' => 3,
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

}
