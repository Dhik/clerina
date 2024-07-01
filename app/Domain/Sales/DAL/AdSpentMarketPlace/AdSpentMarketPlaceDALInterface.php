<?php

namespace App\Domain\Sales\DAL\AdSpentMarketPlace;

use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Requests\AdSpentMarketPlaceRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface AdSpentMarketPlaceDALInterface extends BaseDALInterface
{
    /**
     * Return AdSpentMarketPlace data for DataTable
     */
    public function getAdSpentMarketPlaceDataTable(): Builder;

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentMarketPlaceByDate(string $date, int $tenantId): Collection;

    /**
     * Get AdSpentMarketPlace by date range
     */
    public function getAdSpentMarketPlaceByDateRange($startDate, $endDate, int $tenantId): Collection;

    /**
     * Create new AdSpentMarketPlace data
     */
    public function createAdSpentMarketPlace(AdSpentMarketPlaceRequest $request, int $tenantId): AdSpentMarketPlace;

    /**
     * Update AdSpentMarketPlace data
     */
    public function updateAdSpentMarketPlace(
        AdSpentMarketPlace $adSpentMarketPlace,
        AdSpentMarketPlaceRequest $request
    ): AdSpentMarketPlace;

    /**
     * Delete AdSpentMarketPlace data
     */
    public function deleteAdSpentMarketPlace(AdSpentMarketPlace $adSpentMarketPlace): void;

    /**
     * Sum total adSpent by date
     */
    public function sumTotalAdSpentPerDay($date, int $tenantId): mixed;

    /**
     * Check if adSpent have sales channel
     */
    public function checkAdSpentBySalesChannel(int $salesChannelId, int $tenantId): ?AdSpentMarketPlace;
}
