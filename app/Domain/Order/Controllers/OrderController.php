<?php

namespace App\Domain\Order\Controllers;

use App\Domain\Order\BLL\Order\OrderBLLInterface;
use App\Domain\Order\DAL\Order\OrderDALInterface;
use App\Domain\Order\Exports\OrdersExport;
use App\Domain\Order\Exports\OrderTemplateExport;
use App\Domain\Order\Models\Order;
use App\Domain\Order\Requests\OrderStoreRequest;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Http\Controllers\Controller;
use Auth;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Yajra\DataTables\DataTables;
use Yajra\DataTables\Utilities\Request;

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
                return number_format($row->price, 0, ',', '.');
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
}
