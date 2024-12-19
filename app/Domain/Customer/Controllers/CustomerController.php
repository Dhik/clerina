<?php

namespace App\Domain\Customer\Controllers;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Customer\Models\Customer;
use App\Domain\Order\Models\Order;
use App\Domain\Customer\Models\CustomersAnalysis;
use App\Domain\Tenant\Models\Tenant;
use App\Domain\Customer\Models\CustomerNote;
use App\Domain\Customer\Requests\CustomerRequest;
use App\Domain\User\Enums\PermissionEnum;
use App\Domain\User\Enums\RoleEnum;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application as ApplicationAlias;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Yajra\DataTables\DataTables;
use Carbon\Carbon; 
use Yajra\DataTables\Utilities\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use App\Domain\Sales\Services\GoogleSheetService;

use App\Domain\Customer\Exports\CustomersExport;

class CustomerController extends Controller
{
    protected $googleSheetService;
    public function __construct(protected CustomerBLLInterface $customerBLL, GoogleSheetService $googleSheetService)
    {
        $this->googleSheetService = $googleSheetService;
    }

    /**
     * @return JsonResponse
     */
    public function countOrderByPhoneNumber(): JsonResponse
    {
        return response()->json($this->customerBLL->countOrderByPhoneNumber());
    }

    /**
     * @throws Exception
     */
    public function getCustomer(Request $request): JsonResponse
    {
        $this->authorize('viewCustomer', Customer::class);

        $query = $this->customerBLL->getCustomerDatatable($request, Auth::user()->current_tenant_id);

        return DataTables::of($query)
            ->addColumn('tenant_name', function ($row) {
                return $row->tenant_name;
            })
            ->addColumn('actions', function ($row) {
                return '<a href="'. $row->wa_link .'" class="btn btn-success btn-sm" target="_blank">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="' . route('customer.show', $row->id) . '" class="btn btn-primary btn-sm" target="_blank">
                            <i class="fas fa-eye"></i>
                        </a>';
            })
            ->rawColumns(['actions'])
            ->toJson();
    }



    /**
     * Return customer index page
     */
    public function index(): View|Factory|ApplicationAlias
    {
        $this->authorize('viewCustomer', Customer::class);

        // Fetch active tenants
        $tenants = Tenant::select('id', 'name')->where('status', 'active')->orderBy('name')->get();

        return view('admin.customer.index', compact('tenants'));
    }

    /**
     * Return detail customer
     */
    public function show(Customer $customer): View|Application|Factory|ApplicationAlias
    {
        return view('admin.customer.show', compact('customer'));
    }

    public function export(Request $request): BinaryFileResponse
    {
        $tenantId = Auth::user()->current_tenant_id;
        return (new CustomersExport($tenantId))->download('customers.xlsx');
    }
    public function getCustomerCount(Request $request): JsonResponse
    {
        $type = $request->get('type', 'daily'); // Default ke 'daily'

        $query = Order::query()
        ->join('customers', function ($join) {
            $join->on('orders.customer_name', '=', 'customers.name')
                ->on('orders.customer_phone_number', '=', 'customers.phone_number');
        });

        if ($type === 'daily') {
            $data = $query->selectRaw('DATE(orders.date) as period, COUNT(DISTINCT customers.id) as customer_count')
                ->groupBy('period')
                ->orderBy('period', 'ASC')
                ->get();
        } else {
            $data = $query->selectRaw('DATE_FORMAT(orders.date, "%Y-%m") as period, COUNT(DISTINCT customers.id) as customer_count')
                ->groupBy('period')
                ->orderBy('period', 'ASC')
                ->get();
        }

        return response()->json($data);
    }

    public function getCustomerOrders(Request $request): JsonResponse
    {
        $type = $request->get('type', 'daily');

        $query = Order::query()
        ->join('customers', function ($join) {
            $join->on('orders.customer_name', '=', 'customers.name')
                ->on('orders.customer_phone_number', '=', 'customers.phone_number');
        });
        if ($type === 'daily') {
            $data = $query->selectRaw('DATE(orders.date) as period')
                ->selectRaw('SUM(IF(customers.count_orders = 1, 1, 0)) as first_timer_count')
                ->selectRaw('SUM(IF(customers.count_orders > 1, 1, 0)) as repeated_order_count')
                ->groupBy('period')
                ->orderBy('period', 'ASC')
                ->get();
        } else {
            $data = $query->selectRaw('DATE_FORMAT(orders.date, "%Y-%m") as period')
                ->selectRaw('SUM(IF(customers.count_orders = 1, 1, 0)) as first_timer_count')
                ->selectRaw('SUM(IF(customers.count_orders > 1, 1, 0)) as repeated_order_count')
                ->groupBy('period')
                ->orderBy('period', 'ASC')
                ->get();
        }

        return response()->json($data);
    }
    
    public function getChurnedCustomers(): JsonResponse
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        // Count distinct churned customers
        $churnedCustomersCount = Customer::whereDoesntHave('orders', function ($query) use ($sixMonthsAgo) {
            $query->where('date', '>=', $sixMonthsAgo);
        })->distinct('id')->count('id');

        // Count total distinct customers
        $totalCustomersCount = Customer::distinct('id')->count('id');

        // Calculate churn rate
        $churnRate = $totalCustomersCount > 0 
            ? ($churnedCustomersCount / $totalCustomersCount) * 100 
            : 0;

        // Calculate customer lifespans (average, max, min)
        $lifespans = Customer::join('orders', function ($join) {
                $join->on('customers.name', '=', 'orders.customer_name')
                     ->on('customers.phone_number', '=', 'orders.customer_phone_number');
            })
            ->selectRaw('DATEDIFF(MAX(orders.date), MIN(orders.date)) as lifespan')
            ->groupBy('customers.id')
            ->get()
            ->pluck('lifespan');

        $customerLifespanDays = $lifespans->avg();
        $maxLifespan = $lifespans->max();
        $minLifespan = $lifespans->min();

        // Calculate average customer lifetime value (CLV)
        $avgCLV = Customer::join('orders', function ($join) {
                $join->on('customers.name', '=', 'orders.customer_name')
                     ->on('customers.phone_number', '=', 'orders.customer_phone_number');
            })
            ->selectRaw('AVG(orders.amount) * COUNT(orders.id) * DATEDIFF(MAX(orders.date), MIN(orders.date)) as clv')
            ->groupBy('customers.id')
            ->get()
            ->avg('clv');

        // Calculate repeat purchase rate
        $repeatPurchaseRate = Customer::join('orders', function ($join) {
                $join->on('customers.name', '=', 'orders.customer_name')
                     ->on('customers.phone_number', '=', 'orders.customer_phone_number');
            })
            ->selectRaw('COUNT(orders.id) > 1 as repeat_customer')
            ->groupBy('customers.id')
            ->get()
            ->pluck('repeat_customer')
            ->filter(fn($value) => $value) // Only keep customers with more than one purchase
            ->count() / $totalCustomersCount * 100;

        return response()->json([
            'churned_customers' => $churnedCustomersCount,
            'churn_rate' => $churnRate,
            'average_customer_lifespan_days' => $customerLifespanDays,
            'max_customer_lifespan_days' => $maxLifespan,
            'min_customer_lifespan_days' => $minLifespan,
            'average_customer_lifetime_value' => $avgCLV,
            'repeat_purchase_rate' => $repeatPurchaseRate
        ]);
    }
}
