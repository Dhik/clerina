<?php

namespace App\Domain\Sales\DAL\Visit;

use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Requests\VisitStoreRequest;
use App\DomainUtils\BaseDAL\BaseDALInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface VisitDALInterface extends BaseDALInterface
{
    /**
     * Return visit data for DataTable
     */
    public function getVisitDataTable(): Builder;

    /**
     * Return visit by date
     */
    public function getVisitByDate(string $date, int $tenantId): Collection;

    /**
     * Create new visit data
     */
    public function createVisit(VisitStoreRequest $request, int $tenantId): Visit;

    /**
     * Update visit data
     */
    public function updateVisit(Visit $visit, VisitStoreRequest $request): Visit;

    /**
     * Delete visit data
     */
    public function deleteVisit(Visit $visit): void;

    /**
     * Sum total visit by date
     */
    public function sumTotalVisitPerDay($date, int $tenantId): mixed;

    /**
     * Get visit by date range
     */
    public function getVisitByDateRange($startDate, $endDate, $tenantId): Collection;

    /**
     * Check if visit have sales channel
     */
    public function checkVisitBySalesChannel(int $salesChannelId, int $tenantId): ?Visit;
}
