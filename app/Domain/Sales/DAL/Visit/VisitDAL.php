<?php

namespace App\Domain\Sales\DAL\Visit;

use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Requests\VisitStoreRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class VisitDAL extends BaseDAL implements VisitDALInterface
{
    public function __construct(protected Visit $visit)
    {
    }

    /**
     * Return visit data for DataTable
     */
    public function getVisitDataTable(): Builder
    {
        return $this->visit->query()->with('salesChannel');
    }

    /**
     * Return visit by date
     */
    public function getVisitByDate(string $date, int $tenantId): Collection
    {
        return $this->visit->with('salesChannel')
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get visit by date range
     */
    public function getVisitByDateRange($startDate, $endDate, $tenantId): Collection
    {
        return $this->visit->with('salesChannel')
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', Carbon::parse($startDate))
            ->where('date', '<=', Carbon::parse($endDate))
            ->orderBy('date', 'ASC')
            ->get();
    }

    /**
     * Create new visit data
     */
    public function createVisit(VisitStoreRequest $request, int $tenantId): Visit
    {
        return $this->visit->updateOrCreate([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
            'sales_channel_id' => $request->input('sales_channel_id'),
            'tenant_id' => $tenantId,
        ], [
            'visit_amount' => $request->input('visit_amount'),
        ]);
    }

    /**
     * Update visit data
     */
    public function updateVisit(Visit $visit, VisitStoreRequest $request): Visit
    {
        $visit->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $visit->sales_channel_id = $request->input('sales_channel_id');
        $visit->visit_amount = $request->input('visit_amount');
        $visit->update();

        return $visit;
    }

    /**
     * Delete visit data
     */
    public function deleteVisit(Visit $visit): void
    {
        $visit->delete();
    }

    /**
     * Sum total visit by date
     */
    public function sumTotalVisitPerDay($date, int $tenantId): mixed
    {
        return $this->visit
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->sum('visit_amount');
    }

    /**
     * Check if visit have sales channel
     */
    public function checkVisitBySalesChannel(int $salesChannelId, int $tenantId): ?Visit
    {
        return $this->visit
            ->where('tenant_id', $tenantId)
            ->where('sales_channel_id', $salesChannelId)
            ->first();
    }
}
