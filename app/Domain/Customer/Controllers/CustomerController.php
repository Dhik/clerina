<?php

namespace App\Domain\Customer\Controllers;

use App\Domain\Customer\BLL\Customer\CustomerBLLInterface;
use App\Domain\Customer\Models\Customer;
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
}
