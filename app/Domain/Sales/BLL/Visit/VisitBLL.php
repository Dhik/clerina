<?php

namespace App\Domain\Sales\BLL\Visit;

use App\Domain\Sales\BLL\Sales\SalesBLLInterface;
use App\Domain\Sales\BLL\SalesChannel\SalesChannelBLLInterface;
use App\Domain\Sales\DAL\Visit\VisitDAL;
use App\Domain\Sales\Models\Visit;
use App\Domain\Sales\Requests\VisitStoreRequest;
use App\DomainUtils\BaseBLL\BaseBLL;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class VisitBLL extends BaseBLL implements VisitBLLInterface
{
    public function __construct(
        protected SalesBLLInterface $salesBLL,
        protected SalesChannelBLLInterface $salesChannelBLL,
        protected VisitDAL $visitDAL
    ) {
    }

    /**
     * Return visit data for DataTable
     */
    public function getVisitDataTable(Request $request, int $tenantId): Builder
    {
        $queryVisit = $this->visitDAL->getVisitDataTable();

        $queryVisit->where('tenant_id', $tenantId);

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDate = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');

            $queryVisit->where('date', '>=', $startDate)
                ->where('date', '<=', $endDate);
        }

        if (! is_null($request->input('filterChannel'))) {
            $queryVisit
                ->where('sales_channel_id', $request->input('filterChannel'));
        }

        return $queryVisit;
    }

    /**
     * Retrieves sales recap information based on the provided request.
     */
    public function getVisitRecap(Request $request, int $tenantId): array
    {
        $startDateString = Carbon::now()->startOfMonth();
        $endDateString = Carbon::now()->endOfMonth();

        if (! is_null($request->input('filterDates'))) {
            [$startDateString, $endDateString] = explode(' - ', $request->input('filterDates'));
            $startDateString = Carbon::createFromFormat('d/m/Y', $startDateString)->format('Y-m-d');
            $endDateString = Carbon::createFromFormat('d/m/Y', $endDateString)->format('Y-m-d');
        }

        // Get data by date
        $visit = $this->visitDAL->getVisitByDateRange($startDateString, $endDateString, $tenantId);

        // Group by sales channel
        $visitGrouped = $visit->groupBy('salesChannel.name');

        // Create recap visit by channel
        $visitBySalesChannel = $visitGrouped->map(function ($item) {
            return $item->sum('visit_amount');
        });

        $sumVisitByDate = $visit->groupBy('date')->map(function ($visits) {
            return $visits->sum('visit_amount');
        });

        // Group visits by sales channel and then by date
        $sumVisitByChannelAndDate = $visit->groupBy(function ($channel) {
            return $channel->salesChannel->name;
        })->map(function ($channelVisits) {
            return $channelVisits->groupBy('date')->map(function ($visits) {
                return $visits->sum('visit_amount');
            });
        });

        $salesChannels = $this->salesChannelBLL->getSalesChannel();

        $finalTotalBySalesChannel = [];
        foreach ($salesChannels as $channel) {
            $finalTotalBySalesChannel[$channel->name] = $visitBySalesChannel[$channel->name] ?? 0;
        }

        $finalGroupedData = [];
        foreach ($salesChannels as $channel) {
            $finalGroupedData[$channel->name] = $sumVisitByChannelAndDate[$channel->name] ?? [];
        }

        return [
            'total' => $visitBySalesChannel->sum(),
            'bySalesChannel' => $finalTotalBySalesChannel,
            'visit' => $sumVisitByDate,
            'visitGrouped' => $finalGroupedData,
        ];
    }

    /**
     * Return visit by date
     */
    public function getVisitByDate(string $date, int $tenantId): Collection
    {
        return $this->visitDAL->getVisitByDate($date, $tenantId);
    }

    /**
     * Create new visit data
     *
     * @throws Exception
     */
    public function createVisit(VisitStoreRequest $request, int $tenantId): Visit
    {
        try {
            DB::beginTransaction();

            $visitData = $this->visitDAL->createVisit($request, $tenantId);
            $this->salesBLL->createSales($visitData->date, $tenantId);

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }

        return $visitData;
    }

    /**
     * Update visit data
     */
    public function updateVisit(Visit $visit, VisitStoreRequest $request): Visit
    {
        return $this->visitDAL->updateVisit($visit, $request);
    }

    /**
     * Delete visit data
     */
    public function deleteVisit(Visit $visit): void
    {
        $this->visitDAL->deleteVisit($visit);
    }
}
