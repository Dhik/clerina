<?php

namespace App\Domain\Marketing\Controllers;

use App\Domain\Marketing\BLL\Marketing\MarketingBLLInterface;
use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLLInterface;
use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Exports\MarketingExport;
use App\Domain\Marketing\Exports\MarketingTemplateExport;
use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Requests\BrandingStoreRequest;
use App\Domain\Marketing\Requests\MarketingStoreRequest;
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

class MarketingController extends Controller
{
    public function __construct(
        protected MarketingBLLInterface $marketingBLL,
        protected MarketingCategoryBLLInterface $marketingCategoryBLL
    ) {
    }

    /**
     * @throws Exception
     */
    public function get(Request $request): JsonResponse
    {
        $this->authorize('viewAnyMarketing', Marketing::class);

        $marketingQuery = $this->marketingBLL->getMarketingDataTable($request, Auth::user()->current_tenant_id);

        return DataTables::of($marketingQuery)
            ->addColumn('type_transform', function ($row) {
                return ucfirst($row->type);
            })
            ->addColumn('marketingCategory', function ($row) {
                return $row->marketingCategory->name ?? '-';
            })
            ->addColumn('marketingSubCategory', function ($row) {
                return $row->marketingSubCategory->name ?? '-';
            })
            ->addColumn('amountFormatted', function ($row) {
                return number_format($row->amount, 0, ',', '.');
            })
            ->addColumn(
                'actions',
                '<button class="btn btn-primary btn-sm updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-sm deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>'
            )
            ->rawColumns(['actions'])
            ->toJson();
    }

    /**
     * Display a listing of marketing.
     */
    public function index(): View|\Illuminate\Foundation\Application|Factory|Application
    {
        $this->authorize('viewAnyMarketing', Marketing::class);

        $marketingTypes = MarketingCategoryTypeEnum::Category;
        $marketingCategories = $this->marketingCategoryBLL->getMarketingCategories();
        $brandingCategories = $this->marketingCategoryBLL->getBrandingCategories();

        return view('admin.marketing.index', compact(
            'marketingTypes',
            'marketingCategories',
            'brandingCategories'
        ));
    }

    /**
     * Create marketing data type branding
     */
    public function storeBrandingData(BrandingStoreRequest $request): JsonResponse
    {
        $this->authorize('createMarketing', Marketing::class);

        return response()->json($this->marketingBLL->createBranding($request, Auth::user()->current_tenant_id));
    }

    /**
     * Create marketing data type marketing
     */
    public function storeMarketingData(MarketingStoreRequest $request): JsonResponse
    {
        $this->authorize('createMarketing', Marketing::class);

        return response()->json($this->marketingBLL->createMarketing($request, Auth::user()->current_tenant_id));
    }

    /**
     * Update marketing data type branding
     */
    public function updateBrandingData(Marketing $marketing, BrandingStoreRequest $request): JsonResponse
    {
        $this->authorize('updateMarketing', Marketing::class);

        $this->marketingBLL->updateBranding($marketing, $request, Auth::user()->current_tenant_id);

        return response()->json($request->all());
    }

    /**
     * Update marketing data type marketing
     */
    public function updateMarketingData(Marketing $marketing, MarketingStoreRequest $request): JsonResponse
    {
        $this->authorize('updateMarketing', Marketing::class);

        $this->marketingBLL->updateMarketing($marketing, $request, Auth::user()->current_tenant_id);

        return response()->json($request->all());
    }

    /**
     * Delete marketing
     */
    public function destroy(Marketing $marketing): JsonResponse
    {
        $this->authorize('deleteMarketing', Marketing::class);

        $this->marketingBLL->deleteMarketing($marketing, Auth::user()->current_tenant_id);

        return response()->json(['message' => trans('messages.success_delete')]);
    }

    /**
     * Retrieves marketing recap information based on the provided request.
     */
    public function getMarketingRecap(Request $request): JsonResponse
    {
        return response()->json($this->marketingBLL->getMarketingRecap($request, Auth::user()->current_tenant_id));
    }

    /**
     * Export order
     */
    public function export(Request $request): Response|BinaryFileResponse
    {
        $this->authorize('viewAnyMarketing', Marketing::class);

        return (new MarketingExport(Auth::user()->current_tenant_id))->forPeriod($request->date)->download('marketings.xlsx');
    }

    /**
     * Template import order
     */
    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('createMarketing', Marketing::class);

        return Excel::download(new MarketingTemplateExport(), 'Marketing Template.xlsx');
    }

    /**
     * Import marketing
     *
     * @throws Exception
     */
    public function import(Request $request): void
    {
        $this->authorize('createMarketing', Marketing::class);

        $this->marketingBLL->importMarketing($request, Auth::user()->current_tenant_id);
    }
}
