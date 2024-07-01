<?php

namespace App\Domain\Marketing\BLL\MarketingCategory;

use App\Domain\Marketing\DAL\Marketing\MarketingDALInterface;
use App\Domain\Marketing\DAL\MarketingCategory\MarketingCategoryDAL;
use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\MarketingCategory;
use App\Domain\Marketing\Models\MarketingSubCategory;
use App\Domain\Marketing\Requests\MarketingCategoryRequest;
use App\Domain\Marketing\Requests\MarketingSubCategoryRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use App\DomainUtils\BaseBLL\BaseBLLFileUtils;
use Illuminate\Database\Eloquent\Builder;

class MarketingCategoryBLL extends BaseBLL implements MarketingCategoryBLLInterface
{
    use BaseBLLFileUtils;

    public function __construct(
        MarketingCategoryDAL $marketingCategoryDAL,
        protected MarketingDALInterface $marketingDAL
    ) {
        $this->dal = $marketingCategoryDAL;
    }

    /**
     * Return marketing category for DataTable
     */
    public function getMarketingCategoryDataTable(): Builder
    {
        return $this->dal->getMarketingCategoryDataTable();
    }

    /**
     * Create new marketing category
     */
    public function storeMarketingCategory(MarketingCategoryRequest $request): MarketingCategory
    {
        return $this->dal->storeMarketingCategory($request);
    }

    /**
     * Update marketing category
     */
    public function updateMarketingCategory(
        MarketingCategory $marketingCategory,
        MarketingCategoryRequest $request
    ): MarketingCategory {
        return $this->dal->updateMarketingCategory($marketingCategory, $request);
    }

    /**
     * Delete marketing category
     */
    public function deleteMarketingCategory(MarketingCategory $marketingCategory): bool
    {
        $checkMarketing = $this->marketingDAL->checkMarketingByCategory($marketingCategory->id);

        if (! empty($checkMarketing)) {
            return false;
        }

        $checkMarketingSubCategory = $this->dal->checkMarketingSubCategoryByCategoryId($marketingCategory->id);

        if (! empty($checkMarketingSubCategory)) {
            return false;
        }

        $this->dal->deleteMarketingCategory($marketingCategory);

        return true;
    }

    /**
     * Return marketing sub category for DataTable
     */
    public function getMarketingSubCategoryDataTable(int $marketingCategoryId): Builder
    {
        return $this->dal->getMarketingSubCategoryDataTable($marketingCategoryId);
    }

    /**
     * Create new marketing sub category
     */
    public function storeMarketingSubCategory(MarketingSubCategoryRequest $request): MarketingSubCategory
    {
        return $this->dal->storeMarketingSubCategory($request);
    }

    /**
     * Update marketing sub category
     */
    public function updateMarketingSubCategory(
        MarketingSubCategory $marketingSubCategory,
        MarketingSubCategoryRequest $request
    ): MarketingSubCategory {
        return $this->dal->updateMarketingSubCategory($marketingSubCategory, $request);
    }

    /**
     * Delete marketing sub category
     */
    public function deleteMarketingSubCategory(MarketingSubCategory $marketingSubCategory): bool
    {
        $checkMarketing = $this->marketingDAL->checkMarketingBySubCategory($marketingSubCategory->id);

        if (! empty($checkMarketing)) {
            return false;
        }

        $this->dal->deleteMarketingSubCategory($marketingSubCategory);

        return true;
    }

    /**
     * Return marketing categories and subcategories
     */
    public function getMarketingCategories(): mixed
    {
        return $this->dal->getMarketingCategories()->where('type', MarketingCategoryTypeEnum::Marketing);
    }

    /**
     * Return branding categories and subcategories
     */
    public function getBrandingCategories(): mixed
    {
        return $this->dal->getMarketingCategories()->where('type', MarketingCategoryTypeEnum::Branding);
    }

    /**
     * Return marketing categories
     */
    public function getAllMarketingCategories(): mixed
    {
        return $this->dal->getMarketingCategories();
    }

    /**
     * Return marketing sub categories
     */
    public function getAllMarketingSubCategories(): mixed
    {
        return $this->dal->getMarketingSubCategories();
    }
}
