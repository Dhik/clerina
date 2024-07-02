<?php

namespace App\Domain\Marketing\Controllers;

use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLLInterface;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use App\Domain\Marketing\Requests\MarketingSubCategoryRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class MarketingSubCategoryController extends Controller
{
    public function __construct(public MarketingCategoryBLLInterface $marketingCategoryBLL)
    {
    }

    /**
     * @throws Exception
     */
    public function get(int $marketingCategoryId): JsonResponse
    {
        $this->authorize('viewAnyMarketingCategory', MarketingCategory::class);

        $marketingQuery = $this->marketingCategoryBLL->getMarketingSubCategoryDataTable($marketingCategoryId);

        return DataTables::of($marketingQuery)
            ->addColumn(
                'actions',
                '<button class="btn btn-success btn-xs updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>'
            )
            ->rawColumns(['actions'])
            ->make();
    }

    /**
     * Create new marketing sub category
     */
    public function store(MarketingSubCategoryRequest $request): JsonResponse
    {
        $this->authorize('createMarketingCategory', MarketingCategory::class);

        $this->marketingCategoryBLL->storeMarketingSubCategory($request);

        return response()->json($request->all());
    }

    /**
     * Update marketing sub category
     */
    public function update(
        MarketingSubCategory $marketingSubCategory,
        MarketingSubCategoryRequest $request
    ): JsonResponse {
        $this->authorize('updateMarketingCategory', MarketingCategory::class);

        $this->marketingCategoryBLL->updateMarketingSubCategory($marketingSubCategory, $request);

        return response()->json($request->all());
    }

    /**
     * Delete marketing sub category
     */
    public function delete(MarketingSubCategory $marketingSubCategory): JsonResponse
    {
        $this->authorize('deleteMarketingCategory', MarketingCategory::class);

        $result = $this->marketingCategoryBLL->deleteMarketingSubCategory($marketingSubCategory);

        if (! $result) {
            return response()->json(['message' => trans('messages.marketing_sub_category_failed_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
