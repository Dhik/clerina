<?php

namespace App\Domain\Sales\DAL\AdSpentMarketPlace;

use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Requests\AdSpentMarketPlaceRequest;
use App\DomainUtils\BaseDAL\BaseDAL;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class AdSpentMarketPlaceDAL extends BaseDAL implements AdSpentMarketPlaceDALInterface
{
    public function __construct(protected AdSpentMarketPlace $adSpentMarketPlace)
    {
    }

    /**
     * Return AdSpentMarketPlace data for DataTable
     */
    public function getAdSpentMarketPlaceDataTable(): Builder
    {
        return $this->adSpentMarketPlace->query()->with('salesChannel');
    }

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentMarketPlaceByDate(string $date, int $tenantId): Collection
    {
        return $this->adSpentMarketPlace->with('salesChannel')
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->orderBy('date', 'asc')
            ->get();
    }

    /**
     * Get AdSpentMarketPlace by date range
     */
    public function getAdSpentMarketPlaceByDateRange($startDate, $endDate, int $tenantId): Collection
    {
        return $this->adSpentMarketPlace->with('salesChannel')
            ->where('tenant_id', $tenantId)
            ->where('date', '>=', Carbon::parse($startDate))
            ->where('date', '<=', Carbon::parse($endDate))
            ->orderBy('date', 'ASC')
            ->get();
    }

    /**
     * Create new AdSpentMarketPlace data
     */
    public function createAdSpentMarketPlace(AdSpentMarketPlaceRequest $request, int $tenantId): AdSpentMarketPlace
    {
        return $this->adSpentMarketPlace->updateOrCreate([
            'date' => Carbon::createFromFormat('d/m/Y', $request->input('date'))->format('Y-m-d'),
            'sales_channel_id' => $request->input('sales_channel_id'),
            'tenant_id' => $tenantId,
        ], [
            'amount' => $request->input('amount'),
        ]);
    }

    /**
     * Update AdSpentMarketPlace data
     */
    public function updateAdSpentMarketPlace(
        AdSpentMarketPlace $adSpentMarketPlace,
        AdSpentMarketPlaceRequest $request
    ): AdSpentMarketPlace {
        $adSpentMarketPlace->date = Carbon::createFromFormat('d/m/Y', $request->input('date'));
        $adSpentMarketPlace->sales_channel_id = $request->input('sales_channel_id');
        $adSpentMarketPlace->amount = $request->input('amount');
        $adSpentMarketPlace->update();

        return $adSpentMarketPlace;
    }

    /**
     * Delete AdSpentMarketPlace data
     */
    public function deleteAdSpentMarketPlace(AdSpentMarketPlace $adSpentMarketPlace): void
    {
        $adSpentMarketPlace->delete();
    }

    /**
     * Sum total adSpent by date
     */
    public function sumTotalAdSpentPerDay($date, int $tenantId): mixed
    {
        return $this->adSpentMarketPlace
            ->where('tenant_id', $tenantId)
            ->where('date', Carbon::parse($date))
            ->sum('amount');
    }

    /**
     * Check if adSpent have sales channel
     */
    public function checkAdSpentBySalesChannel(int $salesChannelId, int $tenantId): ?AdSpentMarketPlace
    {
        return $this->adSpentMarketPlace
            ->where('tenant_id', $tenantId)
            ->where('sales_channel_id', $salesChannelId)
            ->first();
    }
}
