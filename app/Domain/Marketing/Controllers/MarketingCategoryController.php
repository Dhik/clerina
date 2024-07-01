<?php

namespace App\Domain\Marketing\Controllers;

use App\Domain\Marketing\BLL\MarketingCategory\MarketingCategoryBLLInterface;
use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Requests\MarketingCategoryRequest;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application as ApplicationAlias;
use Illuminate\Http\JsonResponse;
use Yajra\DataTables\DataTables;

class MarketingCategoryController extends Controller
{
    public function __construct(public MarketingCategoryBLLInterface $marketingCategoryBLL)
    {
    }

    /**
     * @throws Exception
     */
    public function get(): JsonResponse
    {
        $this->authorize('viewAnyMarketingCategory', MarketingCategory::class);

        $marketingQuery = $this->marketingCategoryBLL->getMarketingCategoryDataTable();

        return DataTables::of($marketingQuery)
            ->filter(function ($query) {
                $searchParam = request()->search['value'];

                if (! empty($searchParam)) {
                    $query->orWhereHas('marketingSubCategories', function ($query) use ($searchParam) {
                        $query->where('name', 'like', '%'.$searchParam.'%');
                    });
                }
            }, true)
            ->addColumn('type_transform', function ($row) {
                return ucfirst($row->type);
            })
            ->addColumn('marketingSubCategories', function ($row) {
                return $row->marketingSubCategories->map(function ($value, $key) {
                    return '<span class="badge bg-primary">'.$value->name.'</span>';
                })->implode(' ');
            })
            ->addColumn(
                'actions',
                '<a href="{{ URL::route( \'marketingCategories.show\', array( $id )) }}" class="btn btn-primary btn-xs" >
                            <i class="fas fa-eye"></i>
                        </a>
                        <button class="btn btn-success btn-xs updateButton">
                            <i class="fas fa-pencil-alt"></i>
                        </button>
                        <button class="btn btn-danger btn-xs deleteButton">
                            <i class="fas fa-trash-alt"></i>
                        </button>'
            )
            ->rawColumns(['marketingSubCategories', 'actions'])
            ->toJson();
    }

    /**
     * Show index page marketing category
     */
    public function index(): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewAnyMarketingCategory', MarketingCategory::class);

        $marketingCategoryTypes = MarketingCategoryTypeEnum::Category;

        return view('admin.marketingCategory.index', compact('marketingCategoryTypes'));
    }

    /**
     * Create new marketing category
     */
    public function store(MarketingCategoryRequest $request): JsonResponse
    {
        $this->authorize('createMarketingCategory', MarketingCategory::class);

        $this->marketingCategoryBLL->storeMarketingCategory($request);

        return response()->json($request->all());
    }

    /**
     * Show marketing category
     */
    public function show(MarketingCategory $marketingCategory): View|ApplicationAlias|Factory|Application
    {
        $this->authorize('viewMarketingCategory', MarketingCategory::class);

        return view('admin.marketingCategory.show', compact('marketingCategory'));
    }

    /**
     * Update marketing category
     */
    public function update(MarketingCategory $marketingCategory, MarketingCategoryRequest $request): JsonResponse
    {
        $this->authorize('updateMarketingCategory', MarketingCategory::class);

        $this->marketingCategoryBLL->updateMarketingCategory($marketingCategory, $request);

        return response()->json($request->all());
    }

    /**
     * Delete marketing category
     */
    public function delete(MarketingCategory $marketingCategory): JsonResponse
    {
        $this->authorize('deleteMarketingCategory', MarketingCategory::class);

        $result = $this->marketingCategoryBLL->deleteMarketingCategory($marketingCategory);

        if (! $result) {
            return response()->json(['message' => trans('messages.marketing_category_failed_delete')], 422);
        }

        return response()->json(['message' => trans('messages.success_delete')]);
    }
}
