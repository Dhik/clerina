<?php

namespace App\Domain\Sales\Controllers;

use App\Domain\Sales\BLL\AdSpentMarketPlace\AdSpentMarketPlaceBLLInterface;
use App\Domain\Sales\BLL\AdSpentSocialMedia\AdSpentSocialMediaBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Models\Sales;
use App\Domain\Sales\Requests\AdSpentMarketPlaceRequest;
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

class AdSpentMarketPlaceController extends Controller
{
    private mixed $tenantId;

    public function __construct(
        protected AdSpentMarketPlaceBLLInterface $adSpentMarketPlaceBLL,
        protected AdSpentSocialMediaBLLInterface $adSpentSocialMediaBLL,
        protected SalesChannelBLLInterface $salesChannelBLL
    ) {}

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentMarketPlace', AdSpentMarketPlace::class);

        $visitQuery = $this->adSpentMarketPlaceBLL->getAdSpentMarketPlaceDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($visitQuery)
            ->addColumn('salesChannel', function ($row) {
                return $row->salesChannel->name ?? '-';
            })
            ->addColumn('amountFormatted', function ($row) {
                return number_format($row->amount, 0, ',', '.');
            })
            ->toJson();
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAdSpentMarketPlace', AdSpentMarketPlace::class);

        $salesChannels = $this->salesChannelBLL->getSalesChannel();

        return view('admin.adSpentMarketPlace.index', compact('salesChannels'));
    }

    /**
     * Create or update data Ad Spent marketplace
     */
    public function store(AdSpentMarketPlaceRequest $request): JsonResponse
    {
        $this->authorize('createAdSpentMarketPlace', AdSpentMarketPlace::class);

        $this->authorize('createSales', Sales::class);

        return response()->json($this->adSpentMarketPlaceBLL->createAdSpentMarketPlace($request, Auth::user()->current_tenant_id));
    }

    /**
     * Return by date
     */
    public function getByDate(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentMarketPlace', AdSpentMarketPlace::class);

        $date = Carbon::parse($request->input('date'));

        return response()->json([
            'social_media' => $this->adSpentSocialMediaBLL->getAdSpentSocialMediaByDate($date, Auth::user()->current_tenant_id),
            'market_place' => $this->adSpentMarketPlaceBLL->getAdSpentMarketPlaceByDate($date, Auth::user()->current_tenant_id),
        ]);
    }

    /**
     * Retrieves AdSpent marketplace recap information based on the provided request.
     */
    public function getAdSpentRecap(Request $request): JsonResponse
    {
        $this->authorize('viewAdSpentMarketPlace', AdSpentMarketPlace::class);

        return response()->json($this->adSpentMarketPlaceBLL->getAdSpentMarketPlaceRecap($request, Auth::user()->current_tenant_id));
    }
}
