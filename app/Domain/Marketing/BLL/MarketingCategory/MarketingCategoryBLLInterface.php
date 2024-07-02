<?php

namespace App\Domain\Marketing\BLL\MarketingCategory;

use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use App\Domain\Marketing\Requests\MarketingCategoryRequest;
use App\Domain\Marketing\Requests\MarketingSubCategoryRequest;
use App\DomainUtils\BaseBLL\BaseBLLInterface;
use Illuminate\Database\Eloquent\Builder;

interface MarketingCategoryBLLInterface extends BaseBLLInterface
{
    /**
     * Return marketing category for DataTable
     */
    public function getMarketingCategoryDataTable(): Builder;

    /**
     * Create new marketing category
     */
    public function storeMarketingCategory(MarketingCategoryRequest $request): MarketingCategory;

    /**
     * Update marketing category
     */
    public function updateMarketingCategory(
        MarketingCategory $marketingCategory,
        MarketingCategoryRequest $request
    ): MarketingCategory;

    /**
     * Delete marketing category
     */
    public function deleteMarketingCategory(MarketingCategory $marketingCategory): bool;

    /**
     * Return marketing sub category for DataTable
     */
    public function getMarketingSubCategoryDataTable(int $marketingCategoryId): Builder;

    /**
     * Create new marketing sub category
     */
    public function storeMarketingSubCategory(MarketingSubCategoryRequest $request): MarketingSubCategory;

    /**
     * Update marketing sub category
     */
    public function updateMarketingSubCategory(
        MarketingSubCategory $marketingSubCategory,
        MarketingSubCategoryRequest $request
    ): MarketingSubCategory;

    /**
     * Delete marketing sub category
     */
    public function deleteMarketingSubCategory(MarketingSubCategory $marketingSubCategory): bool;

    /**
     * Return all categories and subcategories
     */
    public function getMarketingCategories(): mixed;

    /**
     * Return branding categories and subcategories
     */
    public function getBrandingCategories(): mixed;

    /**
     * Return marketing categories
     */
    public function getAllMarketingCategories(): mixed;

    /**
     * Return marketing sub categories
     */
    public function getAllMarketingSubCategories(): mixed;
}
