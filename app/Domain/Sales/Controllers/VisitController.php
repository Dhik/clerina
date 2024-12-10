<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\BLL\Visit\VisitBLLInterface;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Requests\VisitStoreRequest;
use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    private mixed $tenantId;

    public function __construct(
        protected SalesChannelBLLInterface $salesChannelBLL,
        protected VisitBLLInterface $visitBLL
    ) {}

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewVisit', Visit::class);

        $visitQuery = $this->visitBLL->getVisitDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($visitQuery)
            ->addColumn('salesChannel', function ($row) {
                return $row->salesChannel->name ?? '-';
            })
            ->addColumn('visitAmountFormatted', function ($row) {
                return number_format($row->visit_amount, 0, ',', '.');
            })
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewVisit', Visit::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();

        return view('admin.visit.index', compact('salesChannels'));
    }

    /**
     * Create or update data visit
     */
    public function store(VisitStoreRequest $request): JsonResponse
    {
        $this->authorize('createVisit', Visit::class);

        $this->authorize('createSales', Sales::class);

        return response()->json($this->visitBLL->createVisit($request, Auth::user()->current_tenant_id));
    }

    /**
     * Get visit by date
     */
    public function getVisitByDate(Request $request): JsonResponse
    {
        $this->authorize('viewVisit', Visit::class);

        return response()->json(
            $this->visitBLL->getVisitByDate(
                Carbon::parse($request->input('date')),
                Auth::user()->current_tenant_id
            )
        );
    }

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getVisitRecap(Request $request): JsonResponse
    {
        $this->authorize('viewVisit', Visit::class);

        return response()->json($this->visitBLL->getVisitRecap($request, Auth::user()->current_tenant_id));
    }
}
