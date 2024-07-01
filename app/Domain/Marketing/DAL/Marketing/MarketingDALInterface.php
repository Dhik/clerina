<?php

namespace App\Domain\Marketing\DAL\Marketing;

use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Models\MarketingRecap;
use App\Domain\Marketing\Requests\BrandingStoreRequest;
use App\Domain\Marketing\Requests\MarketingStoreRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface MarketingDALInterface extends BaseDALInterface
{
    /**
     * Return marketing for DataTable
     */
    public function getMarketingDataTable(): Builder;

    /**
     * Create marketing data type branding
     */
    public function createBranding(BrandingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Update marketing data type branding
     */
    public function updateBranding(Marketing $marketing, BrandingStoreRequest $request): Marketing;

    /**
     * Create marketing data type marketing
     */
    public function createMarketing(MarketingStoreRequest $request, int $tenantId): Marketing;

    /**
     * Update marketing data type marketing
     */
    public function updateMarketing(Marketing $marketing, MarketingStoreRequest $request): Marketing;

    /**
     * Delete marketing
     */
    public function deleteMarketing(Marketing $marketing): void;

    /**
     * Create recap marketing
     */
    public function syncMarketingRecap($marketingRecapToCreate, int $tenantId): MarketingRecap;

    /**
     * Get marketing by date range
     */
    public function getMarketingByDateRange($startDate, $endDate, int $tenantId): Collection;

    /**
     * Get marketing by date
     */
    public function gerMarketingByDate($date, int $tenantId): mixed;

    /**
     * Return marketing order by date
     */
    public function getMarketingDailySum($date, int $tenantId): object;

    /**
     * Check marketing by category
     */
    public function checkMarketingByCategory(int $categoryId, int $tenantId): ?Marketing;

    /**
     * Check marketing by category
     */
    public function checkMarketingBySubCategory(int $subCategoryId, int $tenantId): ?Marketing;
}
