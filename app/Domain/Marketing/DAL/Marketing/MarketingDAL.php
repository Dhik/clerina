<?php

namespace App\Domain\Marketing\DAL\Marketing;

use App\Domain\Marketing\Enums\MarketingCategoryTypeEnum;
use App\Domain\Marketing\Models\Marketing;
use App\Domain\Marketing\Models\MarketingRecap;
use App\Domain\Marketing\Requests\BrandingStoreRequest;
use App\Domain\Marketing\Requests\MarketingStoreRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class MarketingDAL extends BaseDAL implements MarketingDALInterface
{
    public function __construct(
        protected Marketing $marketing,
        protected MarketingRecap $recap
    ) {
    }

    /**
     * Return marketing for DataTable
     */
    public function getMarketingDataTable(): Builder
    {
        return $this->marketing->query()->with('marketingCategory', 'marketingSubCategory');
    }

    /**
     * Create marketing data type branding
     */
    public function createBranding(BrandingStoreRequest $request, int $tenantId): Marketing
    {
        return Marketing::create([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'type' => MarketingCategoryTypeEnum::Branding,
            'marketing_category_id' => $request->input('marketing_category_id'),
            'amount' => $request->input('amount'),
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Update marketing data type branding
     */
    public function updateBranding(Marketing $marketing, BrandingStoreRequest $request): Marketing
    {
        $marketing->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $marketing->marketing_category_id = $request->input('marketing_category_id');
        $marketing->amount = $request->input('amount');
        $marketing->update();

        return $marketing;
    }

    /**
     * Create marketing data type marketing
     */
    public function createMarketing(MarketingStoreRequest $request, int $tenantId): Marketing
    {
        return Marketing::create([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date')),
            'type' => MarketingCategoryTypeEnum::Marketing,
            'marketing_category_id' => $request->input('marketing_category_id'),
            'marketing_sub_category_id' => $request->input('marketing_sub_category_id'),
            'amount' => $request->input('amount'),
            'tenant_id' => $tenantId,
        ]);
    }

    /**
     * Update marketing data type marketing
     */
    public function updateMarketing(Marketing $marketing, MarketingStoreRequest $request): Marketing
    {
        $marketing->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $marketing->marketing_category_id = $request->input('marketing_category_id');
        $marketing->marketing_sub_category_id = $request->input('marketing_sub_category_id');
        $marketing->amount = $request->input('amount');
        $marketing->update();

        return $marketing;
    }

    /**
     * Delete marketing
     */
    public function deleteMarketing(Marketing $marketing): void
    {
        $marketing->delete();
    }

    /**
     * Create recap marketing
     */
    public function syncMarketingRecap($marketingRecapToCreate, int $tenantId): MarketingRecap
    {
        return $this->recap->updateOrCreate([
            'date' => Carbon::parse($marketingRecapToCreate['date'])->format('Y-m-d'),
            'tenant_id' => $tenantId,
        ], [
            ...$marketingRecapToCreate,
        ]);
    }

    /**
     * Get marketing by date range
     */
    public function getMarketingByDateRange($startDate, $endDate, int $tenantId): Collection
    {
        return $this->recap->where('date', '>=', Carbon::parse($startDate))
            ->where('tenant_id', $tenantId)
            ->where('date', '<=', Carbon::parse($endDate))
            ->get();
    }

    /**
     * Get marketing by date
     */
    public function gerMarketingByDate($date, int $tenantId): mixed
    {
        return $this->marketing->where('date', Carbon::parse($date))
            ->where('tenant_id', $tenantId)
            ->get();
    }

    /**
     * Return marketing order by date
     */
    public function getMarketingDailySum($date, int $tenantId): object
    {
        $marketingByDate = $this->gerMarketingByDate($date, $tenantId);

        return (object) [
            'total_marketing' => $marketingByDate->where('type', MarketingCategoryTypeEnum::Marketing)->sum('amount'),
            'total_branding' => $marketingByDate->where('type', MarketingCategoryTypeEnum::Branding)->sum('amount'),
        ];
    }

    /**
     * Check marketing by category
     */
    public function checkMarketingByCategory(int $categoryId, int $tenantId): ?Marketing
    {
        return $this->marketing
            ->where('marketing_category_id', $categoryId)
            ->where('tenant_id', $tenantId)
            ->first();
    }

    /**
     * Check marketing by category
     */
    public function checkMarketingBySubCategory(int $subCategoryId, int $tenantId): ?Marketing
    {
        return $this->marketing
            ->where('marketing_sub_category_id', $subCategoryId)
            ->where('tenant_id', $tenantId)
            ->first();
    }
}
