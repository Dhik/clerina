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
use Illuminate\Support\Facades\DB;
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
            ->addColumn('last_order_date', function ($row) {
                return $row->last_order_date ? date('d M Y', strtotime($row->last_order_date)) : '-';
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

    public function getCustomerKPI(Request $request): JsonResponse
    {
        $this->authorize('viewCustomer', Customer::class);
        
        $tenantId = Auth::user()->current_tenant_id;
        $query = Customer::where('tenant_id', $tenantId);
        
        // Apply the same filters as in the DataTable
        // Month filter
        if ($request->has('filterMonth') && !empty($request->input('filterMonth'))) {
            $date = explode('-', $request->input('filterMonth'));
            $year = $date[0];
            $month = $date[1];
            
            $query->whereYear('last_order_date', '=', $year)
                ->whereMonth('last_order_date', '=', $month);
        } else {
            // Default to current month if no month filter is provided
            $query->whereYear('last_order_date', '=', now()->year)
                ->whereMonth('last_order_date', '=', now()->month);
        }
        
        // Count orders filter
        if ($request->has('filterCountOrders') && !empty($request->input('filterCountOrders'))) {
            $query->where('count_orders', '=', $request->input('filterCountOrders'));
        }
        
        // Copy the base query before applying type filter
        $totalQuery = clone $query;
        $total = $totalQuery->count();
        
        // Type breakdown (from the filtered data)
        $new = clone $query;
        $new = $new->where('type', 'New Customer')->count();
        
        $repeated = clone $query;
        $repeated = $repeated->where('type', 'Repeated')->count();
        
        return response()->json([
            'total' => $total,
            'new' => $new,
            'repeated' => $repeated
        ]);
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

    public function cohort_index(): View|Factory|ApplicationAlias
    {
        $this->authorize('viewCustomer', Customer::class);

        // Fetch active tenants
        $tenants = Tenant::select('id', 'name')->where('status', 'active')->orderBy('name')->get();

        return view('admin.customer.cohort_index', compact('tenants'));
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
        })
        ->where('orders.tenant_id', Auth::user()->current_tenant_id);
        
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
    public function getTableauData()
    {
        try {
            $customers = DB::table('customers')
                ->select([
                    'id',
                    'name',
                    'type',
                    'phone_number',
                    'first_order_date',
                    'username',
                    'shipping_address',
                    'city',
                    'province',
                    'count_orders',
                    'aov',
                    'last_order_date',
                    'created_at',
                    'updated_at'
                ])
                ->limit(10)
                ->get();

            // Transform the data for better Tableau compatibility
            $transformedData = $customers->map(function ($customer) {
                return [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'type' => $customer->type,
                    'phone_number' => $customer->phone_number,
                    'first_order_date' => $customer->first_order_date,
                    'username' => $customer->username,
                    'shipping_address' => $customer->shipping_address,
                    'city' => $customer->city,
                    'province' => $customer->province,
                    'count_orders' => (int) $customer->count_orders,
                    'aov' => (int) $customer->aov,
                    'last_order_date' => $customer->last_order_date,
                    'created_at' => $customer->created_at,
                    'updated_at' => $customer->updated_at,
                    // Add calculated fields that might be useful for mapping
                    'customer_value' => $customer->aov * $customer->count_orders,
                    'days_since_first_order' => $customer->first_order_date ? 
                        now()->diffInDays($customer->first_order_date) : null,
                    'days_since_last_order' => $customer->last_order_date ? 
                        now()->diffInDays($customer->last_order_date) : null,
                    'customer_status' => $this->getCustomerStatus($customer->last_order_date)
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $transformedData,
                'count' => $transformedData->count(),
                'message' => 'Customer data retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving customer data: ' . $e->getMessage(),
                'data' => []
            ], 500);
        }
    }

    // 3. Add this helper method to your CustomerController class
    private function getCustomerStatus($lastOrderDate)
    {
        if (!$lastOrderDate) {
            return 'No Orders';
        }
        
        $daysSinceLastOrder = now()->diffInDays($lastOrderDate);
        
        if ($daysSinceLastOrder <= 30) {
            return 'Active';
        } elseif ($daysSinceLastOrder <= 90) {
            return 'At Risk';
        } else {
            return 'Churned';
        }
    }
}
