<?php

namespace App\Domain\Marketing\DAL\MarketingCategory;

use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use App\Domain\Marketing\Requests\MarketingCategoryRequest;
use App\Domain\Marketing\Requests\MarketingSubCategoryRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class MarketingCategoryDAL extends BaseDAL implements MarketingCategoryDALInterface
{
    public function __construct(
        protected MarketingCategory $marketingCategory,
        protected MarketingSubCategory $marketingSubCategory
    ) {
    }

    /**
     * Return marketing category for DataTable
     */
    public function getMarketingCategoryDataTable(): Builder
    {
        return $this->marketingCategory->query()->with('marketingSubCategories');
    }

    /**
     * Create new marketing category
     */
    public function storeMarketingCategory(MarketingCategoryRequest $request): MarketingCategory
    {
        $this->forgetCache();

        return $this->marketingCategory->create($request->only('name', 'type'));
    }

    /**
     * Update marketing category
     */
    public function updateMarketingCategory(
        MarketingCategory $marketingCategory,
        MarketingCategoryRequest $request
    ): MarketingCategory {
        $marketingCategory->name = $request->name;
        $marketingCategory->type = $request->type;
        $marketingCategory->update();

        $this->forgetCache();

        return $marketingCategory;
    }

    /**
     * Delete marketing category
     */
    public function deleteMarketingCategory(MarketingCategory $marketingCategory): void
    {
        $marketingCategory->delete();
        $this->forgetCache();
    }

    /**
     * Return marketing sub category for DataTable
     */
    public function getMarketingSubCategoryDataTable(int $marketingCategoryId): Builder
    {
        return $this->marketingSubCategory->query()->where('marketing_category_id', $marketingCategoryId);
    }

    /**
     * Create new marketing sub category
     */
    public function storeMarketingSubCategory(MarketingSubCategoryRequest $request): MarketingSubCategory
    {
        $this->forgetCache();

        return $this->marketingSubCategory->create($request->only('name', 'marketing_category_id'));
    }

    /**
     * Update marketing sub category
     */
    public function updateMarketingSubCategory(
        MarketingSubCategory $marketingSubCategoryCategory,
        MarketingSubCategoryRequest $request
    ): MarketingSubCategory {
        $marketingSubCategoryCategory->name = $request->name;
        $marketingSubCategoryCategory->update();

        $this->forgetCache();

        return $marketingSubCategoryCategory;
    }

    /**
     * Delete marketing sub category
     */
    public function deleteMarketingSubCategory(MarketingSubCategory $marketingSubCategory): void
    {
        $marketingSubCategory->delete();
        $this->forgetCache();
    }

    /**
     * Return all categories and subcategories
     */
    public function getMarketingCategories(): mixed
    {
        return Cache::rememberForever(MarketingCategoryTypeEnum::AllMarketingCategoryCacheTag, function () {
            return MarketingCategory::with('marketingSubCategories')->orderBy('name')->get();
        });
    }

    /**
     * Return all subcategories
     */
    public function getMarketingSubCategories(): mixed
    {
        return Cache::rememberForever(MarketingCategoryTypeEnum::AllMarketingSubCategoryCacheTag, function () {
            return MarketingSubCategory::orderBy('name')->get();
        });
    }

    /**
     * Forget cache
     */
    protected function forgetCache(): void
    {
        Cache::forget(MarketingCategoryTypeEnum::AllMarketingCategoryCacheTag);
        Cache::forget(MarketingCategoryTypeEnum::AllMarketingSubCategoryCacheTag);
    }

    /**
     * Check marketing sub category by category id
     */
    public function checkMarketingSubCategoryByCategoryId(int $categoryId): ?MarketingSubCategory
    {
        return $this->marketingSubCategory->where('marketing_category_id', $categoryId)->first();
    }
}
