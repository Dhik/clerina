<?php

namespace App\Domain\Sales\BLL\AdSpentMarketPlace;

use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\DAL\AdSpentMarketPlace\AdSpentMarketPlaceDAL;
use App\Domain\Sales\Models\AdSpentMarketPlace;
use App\Domain\Sales\Requests\AdSpentMarketPlaceRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AdSpentMarketPlaceBLL extends BaseBLL implements AdSpentMarketPlaceBLLInterface
{
    public function __construct(
        protected AdSpentMarketPlaceDAL $adSpentMarketPlaceDAL,
        protected SalesBLLInterface $salesBLL,
        protected SalesChannelBLLInterface $salesChannelBLL
    ) {
    }

    /**
     * Return AdSpentMarketPlace data for DataTable
     */
    public function getAdSpentMarketPlaceDataTable(Request $request, int $tenantId): Builder
    {
        $query = $this->adSpentMarketPlaceDAL->getAdSpentMarketPlaceDataTable();

        $query->where('tenant_id', $tenantId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $query->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        if (! is_null($request->input('filterChannel'))) {
            $query
                ->where('sales_channel_id', $request->input('filterChannel'));
        }

        return $query;
    }

    /**
     * Retrieves AdSpent marketplace recap information based on the provided request.
     */
    public function getAdSpentMarketPlaceRecap(Request $request, int $tenantId): array
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        // Get data by date
        $adSpent = $this->adSpentMarketPlaceDAL
            ->getAdSpentMarketPlaceByDateRange($startDateString, $endDateString, $tenantId);

        // Group by AdSpent channel
        $adSpentGrouped = $adSpent->groupBy('salesChannel.name');

        // Create recap AdSpent by channel
        $adSpentBySalesChannel = $adSpentGrouped->map(function ($item) {
            return $item->sum('amount');
        });

        $sumAdSpentByDate = $adSpent->groupBy('date')->map(function ($spent) {
            return $spent->sum('amount');
        });

        // Group Adspent by AdSpent channel and then by date
        $sumAdSpentByChannelAndDate = $adSpent->groupBy(function ($channel) {
            return $channel->salesChannel->name;
        })->map(function ($channelVisits) {
            return $channelVisits->groupBy('date')->map(function ($spent) {
                return $spent->sum('amount');
            });
        });

        $salesChannels = $this->salesChannelBLL->getSalesChannel();

        $finalTotalBySalesChannel = [];
        foreach ($salesChannels as $channel) {
            $finalTotalBySalesChannel[$channel->name] = $adSpentBySalesChannel[$channel->name] ?? 0;
        }

        $finalGroupedData = [];
        foreach ($salesChannels as $channel) {
            $finalGroupedData[$channel->name] = $sumAdSpentByChannelAndDate[$channel->name] ?? [];
        }

        return [
            'total' => $adSpentBySalesChannel->sum(),
            'bySalesChannel' => $finalTotalBySalesChannel,
            'adSpent' => $sumAdSpentByDate,
            'adSpentGrouped' => $finalGroupedData,
        ];
    }

    /**
     * Return AdSpentMarketPlace by date
     */
    public function getAdSpentMarketPlaceByDate(string $date, int $tenantId): Collection
    {
        return $this->adSpentMarketPlaceDAL->getAdSpentMarketPlaceByDate($date, $tenantId);
    }

    /**
     * Create new AdSpentMarketPlace data
     */
    public function createAdSpentMarketPlace(AdSpentMarketPlaceRequest $request, int $tenantId): AdSpentMarketPlace
    {
        try {
            DB::beginTransaction();

            $adSpentData = $this->adSpentMarketPlaceDAL->createAdSpentMarketPlace($request, $tenantId);
            $this->salesBLL->createSales($adSpentData->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $adSpentData;
    }

    /**
     * Update AdSpentMarketPlace data
     */
    public function updateAdSpentMarketPlace(
        AdSpentMarketPlace $adSpentMarketPlace,
        AdSpentMarketPlaceRequest $request
    ): AdSpentMarketPlace {
        return $this->adSpentMarketPlaceDAL->updateAdSpentMarketPlace($adSpentMarketPlace, $request);
    }

    /**
     * Delete AdSpentMarketPlace data
     */
    public function deleteAdSpentMarketPlace(AdSpentMarketPlace $adSpentMarketPlace): void
    {
        $this->adSpentMarketPlaceDAL->deleteAdSpentMarketPlace($adSpentMarketPlace);
    }
}
